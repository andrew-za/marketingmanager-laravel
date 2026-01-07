<?php

namespace App\Services\AI;

use App\Models\AiGeneration;
use App\Models\Brand;
use App\Models\Organization;
use App\Models\Product;
use App\Models\User;

class LabelInspirationService
{
    public function __construct(
        private ContentGenerationService $contentGenerationService,
        private RateLimitingService $rateLimitingService
    ) {}

    /**
     * Generate label inspiration variations for a product or brand
     */
    public function generateLabels(
        Organization $organization,
        User $user,
        ?Product $product = null,
        ?Brand $brand = null,
        string $context = '',
        int $variationCount = 5,
        array $options = []
    ): array {
        $this->checkRateLimit($organization, 'ai_content_generation');
        
        $prompt = $this->buildLabelPrompt($product, $brand, $context, $variationCount);
        $systemPrompt = $this->buildSystemPrompt($brand);

        $generation = $this->contentGenerationService->generateContent(
            $organization,
            $user,
            'label_inspiration',
            $prompt,
            [
                'system_prompt' => $systemPrompt,
                'model' => $options['model'] ?? 'gpt-4',
                'max_tokens' => 1000,
                'temperature' => $options['temperature'] ?? 0.9,
            ],
            [
                'product_id' => $product?->id,
                'brand_id' => $brand?->id,
                'context' => $context,
                'variation_count' => $variationCount,
            ]
        );

        return $this->parseLabelVariations($generation->generated_content, $variationCount);
    }

    /**
     * Build prompt for label generation
     */
    private function buildLabelPrompt(?Product $product, ?Brand $brand, string $context, int $variationCount): string
    {
        $prompt = "Generate {$variationCount} creative label ideas and taglines";

        if ($product) {
            $prompt .= " for the product: {$product->name}";
            if ($product->description) {
                $prompt .= "\nProduct description: {$product->description}";
            }
        }

        if ($brand) {
            $prompt .= "\nBrand: {$brand->name}";
            if ($brand->summary) {
                $prompt .= "\nBrand summary: {$brand->summary}";
            }
        }

        if ($context) {
            $prompt .= "\nContext/Theme: {$context}";
        }

        $prompt .= "\n\nRequirements:";
        $prompt .= "\n- Each label should be creative and memorable";
        $prompt .= "\n- Labels should be concise (5-10 words max)";
        $prompt .= "\n- Include a mix of descriptive, emotional, and action-oriented labels";
        $prompt .= "\n- Format each label on a new line, numbered 1-{$variationCount}";

        return $prompt;
    }

    /**
     * Build system prompt with brand guidelines
     */
    private function buildSystemPrompt(?Brand $brand): string
    {
        if (!$brand) {
            return 'You are a creative marketing copywriter specializing in creating memorable labels and taglines.';
        }

        $guidelines = [];
        
        if ($brand->tone_of_voice) {
            $guidelines[] = "Tone of voice: {$brand->tone_of_voice}";
        }
        
        if ($brand->keywords) {
            $keywords = is_array($brand->keywords) ? implode(', ', $brand->keywords) : $brand->keywords;
            $guidelines[] = "Use these keywords naturally: {$keywords}";
        }
        
        if ($brand->avoid_keywords) {
            $avoid = is_array($brand->avoid_keywords) ? implode(', ', $brand->avoid_keywords) : $brand->avoid_keywords;
            $guidelines[] = "Avoid using these words/phrases: {$avoid}";
        }

        return 'You are a creative marketing copywriter creating labels and taglines for ' . $brand->name . ".\n\n" .
               implode("\n", $guidelines) . "\n\n" .
               "Maintain brand consistency in all labels.";
    }

    /**
     * Parse label variations from generated content
     */
    private function parseLabelVariations(string $content, int $count): array
    {
        $variations = [];
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Remove numbering (1., 1), etc.)
            $line = preg_replace('/^\d+[\.\)]\s*/', '', $line);
            
            // Remove markdown formatting
            $line = preg_replace('/^[-*]\s*/', '', $line);
            
            if (strlen($line) > 5 && strlen($line) < 200) {
                $variations[] = $line;
            }
            
            if (count($variations) >= $count) {
                break;
            }
        }

        // If we don't have enough variations, split by common delimiters
        if (count($variations) < $count) {
            $delimiters = ['|', ';', ','];
            foreach ($delimiters as $delimiter) {
                if (strpos($content, $delimiter) !== false) {
                    $split = explode($delimiter, $content);
                    $variations = array_map('trim', $split);
                    $variations = array_filter($variations, fn($v) => strlen($v) > 5 && strlen($v) < 200);
                    $variations = array_slice($variations, 0, $count);
                    break;
                }
            }
        }

        return array_slice($variations, 0, $count);
    }

    /**
     * Check rate limit for organization
     */
    private function checkRateLimit(Organization $organization, string $feature): void
    {
        if (!$this->rateLimitingService->checkRateLimit($organization, $feature)) {
            throw new \Exception('Rate limit exceeded for AI content generation. Please upgrade your plan.');
        }
    }
}

