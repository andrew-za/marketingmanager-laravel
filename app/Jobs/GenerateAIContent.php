<?php

namespace App\Jobs;

use App\Models\Organization;
use App\Models\User;
use App\Services\AI\ContentGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Generate AI Content Job
 * Processes AI content generation asynchronously
 */
class GenerateAIContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public Organization $organization,
        public User $user,
        public string $type,
        public string $prompt,
        public array $options,
        public array $metadata = []
    ) {}

    public function handle(ContentGenerationService $contentGenerationService): void
    {
        try {
            $contentGenerationService->generateContent(
                $this->organization,
                $this->user,
                $this->type,
                $this->prompt,
                $this->options,
                $this->metadata
            );

            Log::info("AI content generated successfully for type: {$this->type}");
        } catch (\Exception $e) {
            Log::error("Failed to generate AI content: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateAIContent job failed after {$this->tries} attempts: " . $exception->getMessage());
    }
}

