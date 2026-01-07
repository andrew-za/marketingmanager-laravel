<?php

namespace App\Services\Campaign;

use App\Models\Campaign;
use App\Models\User;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Channel;
use App\Models\ScheduledPost;
use App\Repositories\Contracts\CampaignRepositoryInterface;
use App\Services\Campaign\CampaignNotificationService;
use App\Services\AI\ContentGenerationService;
use Illuminate\Support\Facades\DB;

class CampaignService
{
    public function __construct(
        private CampaignRepositoryInterface $repository,
        private CampaignNotificationService $notificationService,
        private ContentGenerationService $aiService
    ) {}

    public function createCampaign(array $data, User $user): Campaign
    {
        return DB::transaction(function () use ($data, $user) {
            $campaign = $this->repository->create([
                ...$data,
                'organization_id' => $user->primaryOrganization()->id,
                'created_by' => $user->id,
            ]);

            $this->notificationService->notifyCampaignCreated($campaign);

            return $campaign;
        });
    }

    public function updateCampaign(Campaign $campaign, array $data): Campaign
    {
        return DB::transaction(function () use ($campaign, $data) {
            $campaign = $this->repository->update($campaign, $data);

            $this->notificationService->notifyCampaignUpdated($campaign);

            return $campaign;
        });
    }

    public function deleteCampaign(Campaign $campaign): bool
    {
        return DB::transaction(function () use ($campaign) {
            return $this->repository->delete($campaign);
        });
    }

    public function publishCampaign(Campaign $campaign): void
    {
        if (!$campaign->isReadyToPublish()) {
            throw new \Exception('Campaign is not ready to be published.');
        }

        DB::transaction(function () use ($campaign) {
            $campaign->markAsPublished();
            $this->notificationService->notifyCampaignPublished($campaign);
        });
    }

    /**
     * Generate campaign plan using AI
     */
    public function generateCampaignPlan(array $data, User $user): array
    {
        $organization = $user->primaryOrganization();
        $brand = isset($data['brand_id']) ? Brand::find($data['brand_id']) : null;
        $product = isset($data['product_id']) ? Product::find($data['product_id']) : null;
        $channels = Channel::whereIn('id', $data['channel_ids'] ?? [])->get();

        $prompt = $this->buildCampaignPlanPrompt($data, $brand, $product, $channels);
        
        $aiResult = $this->aiService->generateContent(
            $organization,
            $user,
            'campaign_plan',
            $prompt,
            [
                'model' => 'gpt-4',
                'max_tokens' => 2000,
                'temperature' => 0.7,
            ],
            [
                'goal_type' => $data['goal_type'] ?? null,
                'brand_id' => $brand?->id,
                'product_id' => $product?->id,
            ]
        );

        $plan = $this->parseCampaignPlan($aiResult->content);

        // Create draft campaign
        $campaign = $this->repository->create([
            'organization_id' => $organization->id,
            'brand_id' => $brand?->id,
            'name' => $data['goal'] ?? 'New Campaign',
            'description' => $data['goal'],
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        // Attach channels
        foreach ($channels as $channel) {
            $campaign->channels()->attach($channel->id, [
                'budget' => 0,
                'spent' => 0,
                'status' => 'pending',
            ]);
        }

        return [
            'campaign_id' => $campaign->id,
            'plan' => $plan,
        ];
    }

    /**
     * Generate content for campaign
     */
    public function generateCampaignContent(Campaign $campaign, User $user, array $channels): array
    {
        $organization = $user->primaryOrganization();
        $posts = [];

        foreach ($channels as $channelId) {
            $channel = Channel::find($channelId);
            if (!$channel) continue;

            $prompt = $this->buildContentPrompt($campaign, $channel);
            
            $aiResult = $this->aiService->generateContent(
                $organization,
                $user,
                'campaign_content',
                $prompt,
                [
                    'model' => 'gpt-4',
                    'max_tokens' => 1000,
                    'temperature' => 0.8,
                ],
                [
                    'campaign_id' => $campaign->id,
                    'channel_id' => $channel->id,
                    'platform' => $channel->platform,
                ]
            );

            $content = $this->parseContent($aiResult->content);
            
            $posts[] = [
                'channel' => $channel->display_name || $channel->platform,
                'platform' => $channel->platform,
                'content' => $content['text'],
                'hashtags' => $content['hashtags'] ?? [],
                'imageSuggestions' => $content['image_suggestions'] ?? [],
                'scheduledAt' => null,
            ];
        }

        return ['posts' => $posts];
    }

    /**
     * Get AI suggestions for campaign goals
     */
    public function getCampaignSuggestions(User $user, ?int $brandId = null, ?int $productId = null): array
    {
        $organization = $user->primaryOrganization();
        $brand = $brandId ? Brand::find($brandId) : null;
        $product = $productId ? Product::find($productId) : null;

        $prompt = "Generate 3-5 campaign goal suggestions for a marketing campaign. ";
        if ($brand) {
            $prompt .= "Brand: {$brand->name}. ";
        }
        if ($product) {
            $prompt .= "Product: {$product->name}. ";
        }
        $prompt .= "Return suggestions as JSON array with 'title', 'description', and 'goalType' fields.";

        $aiResult = $this->aiService->generateContent(
            $organization,
            $user,
            'campaign_suggestions',
            $prompt,
            [
                'model' => 'gpt-4',
                'max_tokens' => 500,
                'temperature' => 0.9,
            ],
            [
                'brand_id' => $brandId,
                'product_id' => $productId,
            ]
        );

        $suggestions = json_decode($aiResult->content, true);
        return is_array($suggestions) ? $suggestions : [];
    }

    private function buildCampaignPlanPrompt(array $data, ?Brand $brand, ?Product $product, $channels): string
    {
        $prompt = "Create a comprehensive marketing campaign plan.\n\n";
        $prompt .= "Campaign Goal: {$data['goal']}\n";
        $prompt .= "Goal Type: {$data['goal_type']}\n";
        
        if ($brand) {
            $prompt .= "Brand: {$brand->name}\n";
        }
        if ($product) {
            $prompt .= "Product: {$product->name}\n";
        }
        
        $prompt .= "Channels: " . $channels->pluck('platform')->join(', ') . "\n\n";
        $prompt .= "Provide a detailed plan including:\n";
        $prompt .= "1. Campaign strategy (2-3 paragraphs)\n";
        $prompt .= "2. Content themes (bullet points)\n";
        $prompt .= "3. Posting schedule recommendations\n";
        $prompt .= "4. Channel-specific recommendations\n\n";
        $prompt .= "Return as JSON with keys: strategy, contentThemes (array), postingSchedule, channelRecommendations (object).";

        return $prompt;
    }

    private function parseCampaignPlan(string $content): array
    {
        $decoded = json_decode($content, true);
        if ($decoded) {
            return $decoded;
        }

        // Fallback parsing if JSON parsing fails
        return [
            'strategy' => $content,
            'contentThemes' => [],
            'postingSchedule' => 'To be determined',
            'channelRecommendations' => [],
        ];
    }

    private function buildContentPrompt(Campaign $campaign, Channel $channel): string
    {
        $prompt = "Create engaging social media content for {$channel->platform}.\n\n";
        $prompt .= "Campaign: {$campaign->name}\n";
        $prompt .= "Goal: {$campaign->description}\n\n";
        $prompt .= "Generate:\n";
        $prompt .= "1. Post copy (optimized for {$channel->platform})\n";
        $prompt .= "2. Relevant hashtags\n";
        $prompt .= "3. Image suggestions\n\n";
        $prompt .= "Return as JSON with keys: text, hashtags (array), image_suggestions (array).";

        return $prompt;
    }

    private function parseContent(string $content): array
    {
        $decoded = json_decode($content, true);
        if ($decoded) {
            return $decoded;
        }

        return [
            'text' => $content,
            'hashtags' => [],
            'image_suggestions' => [],
        ];
    }
}


