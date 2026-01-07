<?php

namespace App\Services\AI;

use App\Models\AiGeneration;
use App\Models\Brand;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ContentGenerationService
{
    public function __construct(
        private AiProviderService $providerService,
        private RateLimitingService $rateLimitingService,
        private AiUsageTrackingService $usageTrackingService
    ) {}

    public function generateSocialMediaPost(
        Organization $organization,
        User $user,
        string $platform,
        string $topic,
        ?Brand $brand = null,
        array $options = []
    ): AiGeneration {
        $this->checkRateLimit($organization, 'ai_content_generation');

        $prompt = $this->buildSocialMediaPrompt($platform, $topic, $brand, $options);
        $systemPrompt = $this->buildSystemPrompt($brand);

        return $this->generateContent(
            $organization,
            $user,
            'content',
            $prompt,
            [
                'system_prompt' => $systemPrompt,
                'model' => $options['model'] ?? 'gpt-4',
                'max_tokens' => $this->getMaxTokensForPlatform($platform),
                'temperature' => $options['temperature'] ?? 0.7,
            ],
            [
                'platform' => $platform,
                'topic' => $topic,
                'brand_id' => $brand?->id,
            ]
        );
    }

    public function generatePressRelease(
        Organization $organization,
        User $user,
        string $topic,
        array $details,
        ?Brand $brand = null,
        array $options = []
    ): AiGeneration {
        $this->checkRateLimit($organization, 'ai_content_generation');

        $prompt = $this->buildPressReleasePrompt($topic, $details, $brand);
        $systemPrompt = $this->buildSystemPrompt($brand);

        return $this->generateContent(
            $organization,
            $user,
            'press_release',
            $prompt,
            [
                'system_prompt' => $systemPrompt,
                'model' => $options['model'] ?? 'gpt-4',
                'max_tokens' => 2000,
                'temperature' => $options['temperature'] ?? 0.6,
            ],
            [
                'topic' => $topic,
                'details' => $details,
                'brand_id' => $brand?->id,
            ]
        );
    }

    public function generateEmailTemplate(
        Organization $organization,
        User $user,
        string $purpose,
        string $audience,
        ?Brand $brand = null,
        array $options = []
    ): AiGeneration {
        $this->checkRateLimit($organization, 'ai_content_generation');

        $prompt = $this->buildEmailPrompt($purpose, $audience, $brand);
        $systemPrompt = $this->buildSystemPrompt($brand);

        return $this->generateContent(
            $organization,
            $user,
            'email',
            $prompt,
            [
                'system_prompt' => $systemPrompt,
                'model' => $options['model'] ?? 'gpt-4',
                'max_tokens' => 1500,
                'temperature' => $options['temperature'] ?? 0.7,
            ],
            [
                'purpose' => $purpose,
                'audience' => $audience,
                'brand_id' => $brand?->id,
            ]
        );
    }

    public function generateBlogPost(
        Organization $organization,
        User $user,
        string $topic,
        string $targetAudience,
        int $wordCount = 1000,
        ?Brand $brand = null,
        array $options = []
    ): AiGeneration {
        $this->checkRateLimit($organization, 'ai_content_generation');

        $prompt = $this->buildBlogPostPrompt($topic, $targetAudience, $wordCount, $brand);
        $systemPrompt = $this->buildSystemPrompt($brand);

        return $this->generateContent(
            $organization,
            $user,
            'blog',
            $prompt,
            [
                'system_prompt' => $systemPrompt,
                'model' => $options['model'] ?? 'gpt-4',
                'max_tokens' => (int)($wordCount * 1.3),
                'temperature' => $options['temperature'] ?? 0.7,
            ],
            [
                'topic' => $topic,
                'target_audience' => $targetAudience,
                'word_count' => $wordCount,
                'brand_id' => $brand?->id,
            ]
        );
    }

    public function generateAdCopy(
        Organization $organization,
        User $user,
        string $product,
        string $platform,
        string $objective,
        ?Brand $brand = null,
        array $options = []
    ): AiGeneration {
        $this->checkRateLimit($organization, 'ai_content_generation');

        $prompt = $this->buildAdCopyPrompt($product, $platform, $objective, $brand);
        $systemPrompt = $this->buildSystemPrompt($brand);

        return $this->generateContent(
            $organization,
            $user,
            'ad_copy',
            $prompt,
            [
                'system_prompt' => $systemPrompt,
                'model' => $options['model'] ?? 'gpt-4',
                'max_tokens' => 500,
                'temperature' => $options['temperature'] ?? 0.8,
            ],
            [
                'product' => $product,
                'platform' => $platform,
                'objective' => $objective,
                'brand_id' => $brand?->id,
            ]
        );
    }

    public function generateContentVariations(
        Organization $organization,
        User $user,
        string $baseContent,
        int $variationCount = 3,
        array $options = []
    ): array {
        $this->checkRateLimit($organization, 'ai_content_generation');

        $prompt = "Generate {$variationCount} variations of the following content. Each variation should maintain the same core message but use different wording, tone, or structure:\n\n{$baseContent}";

        $generation = $this->generateContent(
            $organization,
            $user,
            'content',
            $prompt,
            [
                'model' => $options['model'] ?? 'gpt-4',
                'max_tokens' => strlen($baseContent) * 2,
                'temperature' => $options['temperature'] ?? 0.9,
            ],
            [
                'variation_count' => $variationCount,
                'base_content' => $baseContent,
            ]
        );

        $variations = $this->parseVariations($generation->generated_content, $variationCount);

        return array_map(function ($variation) use ($organization, $user, $generation) {
            return AiGeneration::create([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'type' => 'content',
                'provider' => $generation->provider,
                'model' => $generation->model,
                'prompt' => $generation->prompt,
                'generated_content' => $variation,
                'status' => 'completed',
                'tokens_used' => 0,
                'cost' => 0,
            ]);
        }, $variations);
    }

    /**
     * Generate content using AI provider
     * Public method for use by other services
     */
    public function generateContent(
        Organization $organization,
        User $user,
        string $type,
        string $prompt,
        array $options,
        array $metadata = []
    ): AiGeneration {
        $provider = $this->providerService->getProvider($options['provider'] ?? null);
        
        $aiGeneration = AiGeneration::create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'type' => $type,
            'provider' => $provider->getProviderName(),
            'model' => $options['model'] ?? 'gpt-4',
            'prompt' => $prompt,
            'status' => 'processing',
            'metadata' => $metadata,
        ]);

        try {
            $result = $provider->generateContent($prompt, $options);
            
            $aiGeneration->markAsCompleted(
                $result['content'],
                $result['tokens_used'],
                $result['cost']
            );

            $this->usageTrackingService->logUsage(
                $organization,
                $user,
                $aiGeneration,
                $provider->getProviderName(),
                $result['model'],
                $type,
                $result['tokens_used'],
                $result['cost']
            );

            $this->rateLimitingService->incrementUsage($organization, 'ai_content_generation');
        } catch (\Exception $e) {
            $aiGeneration->markAsFailed($e->getMessage());
            throw $e;
        }

        return $aiGeneration;
    }

    private function buildSocialMediaPrompt(string $platform, string $topic, ?Brand $brand, array $options): string
    {
        $platformGuidelines = [
            'twitter' => 'Keep it concise (280 characters max), use hashtags sparingly, engaging tone',
            'facebook' => 'Conversational tone, can be longer, encourage engagement with questions',
            'instagram' => 'Visual-first thinking, use emojis appropriately, hashtags for discovery',
            'linkedin' => 'Professional tone, value-driven content, longer form acceptable',
            'tiktok' => 'Trendy, fun, authentic voice, use trending sounds/hashtags',
        ];

        $guidelines = $platformGuidelines[strtolower($platform)] ?? 'Engaging and authentic tone';
        
        $brandGuidelines = $brand ? $this->getBrandGuidelines($brand) : '';
        
        return "Create a {$platform} post about: {$topic}\n\nPlatform guidelines: {$guidelines}\n{$brandGuidelines}\n\n" . 
               ($options['tone'] ? "Tone: {$options['tone']}\n" : '') .
               ($options['call_to_action'] ? "Include a call to action: {$options['call_to_action']}\n" : '');
    }

    private function buildPressReleasePrompt(string $topic, array $details, ?Brand $brand): string
    {
        $brandGuidelines = $brand ? $this->getBrandGuidelines($brand) : '';
        
        return "Write a professional press release about: {$topic}\n\n" .
               "Key details:\n" . implode("\n", array_map(fn($k, $v) => "- {$k}: {$v}", array_keys($details), $details)) .
               "\n\n{$brandGuidelines}\n\n" .
               "Format: Headline, Dateline, Lead paragraph, Body paragraphs, Boilerplate, Contact information";
    }

    private function buildEmailPrompt(string $purpose, string $audience, ?Brand $brand): string
    {
        $brandGuidelines = $brand ? $this->getBrandGuidelines($brand) : '';
        
        return "Create an email template for: {$purpose}\n\n" .
               "Target audience: {$audience}\n\n" .
               "{$brandGuidelines}\n\n" .
               "Include: Subject line, Greeting, Body content, Call to action, Closing";
    }

    private function buildBlogPostPrompt(string $topic, string $targetAudience, int $wordCount, ?Brand $brand): string
    {
        $brandGuidelines = $brand ? $this->getBrandGuidelines($brand) : '';
        
        return "Write a blog post about: {$topic}\n\n" .
               "Target audience: {$targetAudience}\n" .
               "Word count: approximately {$wordCount} words\n\n" .
               "{$brandGuidelines}\n\n" .
               "Structure: Engaging headline, Introduction, Main content with subheadings, Conclusion, Call to action";
    }

    private function buildAdCopyPrompt(string $product, string $platform, string $objective, ?Brand $brand): string
    {
        $brandGuidelines = $brand ? $this->getBrandGuidelines($brand) : '';
        
        return "Create ad copy for: {$product}\n\n" .
               "Platform: {$platform}\n" .
               "Objective: {$objective}\n\n" .
               "{$brandGuidelines}\n\n" .
               "Include: Headline, Description, Call to action";
    }

    private function buildSystemPrompt(?Brand $brand): string
    {
        if (!$brand) {
            return 'You are a professional content writer creating high-quality marketing content.';
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

        return 'You are a professional content writer creating content for ' . $brand->name . ".\n\n" .
               implode("\n", $guidelines) . "\n\n" .
               "Maintain brand consistency in all content.";
    }

    private function getBrandGuidelines(Brand $brand): string
    {
        $guidelines = [];
        
        if ($brand->tone_of_voice) {
            $guidelines[] = "Brand tone: {$brand->tone_of_voice}";
        }
        
        if ($brand->guidelines) {
            $guidelines[] = "Brand guidelines: {$brand->guidelines}";
        }
        
        return implode("\n", $guidelines);
    }

    private function getMaxTokensForPlatform(string $platform): int
    {
        return match(strtolower($platform)) {
            'twitter', 'x' => 200,
            'facebook' => 500,
            'instagram' => 400,
            'linkedin' => 800,
            'tiktok' => 300,
            default => 500,
        };
    }

    private function parseVariations(string $content, int $count): array
    {
        $variations = [];
        $lines = explode("\n", $content);
        
        $currentVariation = '';
        foreach ($lines as $line) {
            if (preg_match('/^(variation|version|option)\s*\d+/i', trim($line))) {
                if ($currentVariation) {
                    $variations[] = trim($currentVariation);
                }
                $currentVariation = '';
            } else {
                $currentVariation .= $line . "\n";
            }
        }
        
        if ($currentVariation) {
            $variations[] = trim($currentVariation);
        }

        if (count($variations) < $count) {
            $splitContent = str_split($content, strlen($content) / $count);
            $variations = array_slice($splitContent, 0, $count);
        }

        return array_slice($variations, 0, $count);
    }

    private function checkRateLimit(Organization $organization, string $feature): void
    {
        if (!$this->rateLimitingService->checkRateLimit($organization, $feature)) {
            throw new \Exception('Rate limit exceeded for AI content generation. Please upgrade your plan.');
        }
    }
}


