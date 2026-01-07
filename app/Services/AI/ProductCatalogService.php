<?php

namespace App\Services\AI;

use App\Models\AiGeneration;
use App\Models\Brand;
use App\Models\Organization;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

class ProductCatalogService
{
    public function __construct(
        private ContentGenerationService $contentGenerationService,
        private RateLimitingService $rateLimitingService
    ) {}

    /**
     * Generate product catalog content for selected products
     */
    public function generateCatalog(
        Organization $organization,
        User $user,
        Collection $products,
        ?Brand $brand = null,
        string $format = 'standard',
        array $options = []
    ): AiGeneration {
        $this->checkRateLimit($organization, 'ai_content_generation');
        
        $prompt = $this->buildCatalogPrompt($products, $brand, $format);
        $systemPrompt = $this->buildSystemPrompt($brand);

        return $this->contentGenerationService->generateContent(
            $organization,
            $user,
            'product_catalog',
            $prompt,
            [
                'system_prompt' => $systemPrompt,
                'model' => $options['model'] ?? 'gpt-4',
                'max_tokens' => 3000,
                'temperature' => $options['temperature'] ?? 0.7,
            ],
            [
                'product_ids' => $products->pluck('id')->toArray(),
                'brand_id' => $brand?->id,
                'format' => $format,
                'product_count' => $products->count(),
            ]
        );
    }

    /**
     * Generate individual product descriptions
     */
    public function generateProductDescriptions(
        Organization $organization,
        User $user,
        Collection $products,
        ?Brand $brand = null,
        array $options = []
    ): array {
        $this->checkRateLimit($organization, 'ai_content_generation');
        
        $descriptions = [];
        
        foreach ($products as $product) {
            $prompt = $this->buildProductDescriptionPrompt($product, $brand);
            $systemPrompt = $this->buildSystemPrompt($brand);

            $generation = $this->contentGenerationService->generateContent(
                $organization,
                $user,
                'product_description',
                $prompt,
                [
                    'system_prompt' => $systemPrompt,
                    'model' => $options['model'] ?? 'gpt-4',
                    'max_tokens' => 500,
                    'temperature' => $options['temperature'] ?? 0.7,
                ],
                [
                    'product_id' => $product->id,
                    'brand_id' => $brand?->id,
                ]
            );

            $descriptions[$product->id] = [
                'product' => $product,
                'description' => $generation->generated_content,
                'generation' => $generation,
            ];
        }

        return $descriptions;
    }

    /**
     * Build prompt for catalog generation
     */
    private function buildCatalogPrompt(Collection $products, ?Brand $brand, string $format): string
    {
        $prompt = "Create a product catalog";

        if ($brand) {
            $prompt .= " for {$brand->name}";
        }

        $prompt .= " with the following products:\n\n";

        foreach ($products as $product) {
            $prompt .= "Product: {$product->name}\n";
            if ($product->description) {
                $prompt .= "Current description: {$product->description}\n";
            }
            if ($product->price) {
                $prompt .= "Price: {$product->price}\n";
            }
            if ($product->category) {
                $prompt .= "Category: {$product->category->name}\n";
            }
            $prompt .= "\n";
        }

        $prompt .= "\nFormat requirements:\n";
        
        switch ($format) {
            case 'detailed':
                $prompt .= "- Include detailed descriptions for each product\n";
                $prompt .= "- Add product features and benefits\n";
                $prompt .= "- Include pricing information\n";
                $prompt .= "- Add call-to-action for each product\n";
                break;
            case 'minimal':
                $prompt .= "- Keep descriptions concise (2-3 sentences)\n";
                $prompt .= "- Focus on key selling points\n";
                break;
            default: // standard
                $prompt .= "- Include product name and description\n";
                $prompt .= "- Add key features\n";
                $prompt .= "- Include pricing\n";
                break;
        }

        $prompt .= "\nStructure the catalog with clear sections and professional formatting.";

        return $prompt;
    }

    /**
     * Build prompt for individual product description
     */
    private function buildProductDescriptionPrompt(Product $product, ?Brand $brand): string
    {
        $prompt = "Write a compelling product description for: {$product->name}\n\n";

        if ($product->description) {
            $prompt .= "Current description: {$product->description}\n\n";
        }

        if ($product->price) {
            $prompt .= "Price: {$product->price}\n";
        }

        if ($product->category) {
            $prompt .= "Category: {$product->category->name}\n";
        }

        $prompt .= "\nRequirements:\n";
        $prompt .= "- Engaging and persuasive tone\n";
        $prompt .= "- Highlight key features and benefits\n";
        $prompt .= "- Include a call-to-action\n";
        $prompt .= "- Length: 100-200 words\n";

        return $prompt;
    }

    /**
     * Build system prompt with brand guidelines
     */
    private function buildSystemPrompt(?Brand $brand): string
    {
        if (!$brand) {
            return 'You are a professional product copywriter creating compelling product descriptions and catalogs.';
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

        return 'You are a professional product copywriter creating content for ' . $brand->name . ".\n\n" .
               implode("\n", $guidelines) . "\n\n" .
               "Maintain brand consistency in all product descriptions.";
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

