<?php

namespace App\Services;

use App\Models\Agency;
use App\Models\Organization;
use App\Models\Report;
use App\Models\User;
use App\Services\AI\ContentGenerationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Agency Report Service
 * Handles AI-powered report generation for agency clients
 */
class AgencyReportService
{
    public function __construct(
        private ContentGenerationService $aiContentService
    ) {}

    /**
     * Generate agency report for a client
     */
    public function generateReport(Agency $agency, Organization $organization, string $reportType): array
    {
        $startDate = $this->getStartDateForReportType($reportType);
        $endDate = now();

        // Gather data for report
        $data = $this->gatherClientData($organization, $startDate, $endDate);

        // Generate AI-powered report
        $report = $this->generateAIPoweredReport($organization, $data, $reportType);

        // Store report history
        $this->storeReportHistory($agency, $organization, $reportType, $report);

        return $report;
    }

    /**
     * Get start date based on report type
     */
    private function getStartDateForReportType(string $reportType): \Carbon\Carbon
    {
        return match($reportType) {
            'weekly' => now()->subWeek(),
            'monthly' => now()->subMonth(),
            'quarterly' => now()->subQuarter(),
            default => now()->subMonth(),
        };
    }

    /**
     * Gather client data for report
     */
    private function gatherClientData(Organization $organization, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate): array
    {
        return [
            'campaigns' => $organization->campaigns()
                ->whereBetween('start_date', [$startDate, $endDate])
                ->withCount('scheduledPosts')
                ->get(),
            'scheduled_posts' => $organization->scheduledPosts()
                ->whereBetween('scheduled_at', [$startDate, $endDate])
                ->count(),
            'published_posts' => $organization->publishedPosts()
                ->whereBetween('published_at', [$startDate, $endDate])
                ->count(),
            'tasks' => $organization->tasks()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get(),
            'email_campaigns' => $organization->emailCampaigns()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get(),
        ];
    }

    /**
     * Generate AI-powered report content
     */
    private function generateAIPoweredReport(Organization $organization, array $data, string $reportType): array
    {
        $prompt = $this->buildReportPrompt($organization, $data, $reportType);

        try {
            $user = auth()->user() ?? $organization->owner;
            
            $aiGeneration = $this->aiContentService->generateContent(
                $organization,
                $user,
                'report',
                $prompt,
                [
                    'max_tokens' => 2000,
                    'temperature' => 0.7,
                ],
                [
                    'report_type' => $reportType,
                ]
            );

            return $this->parseAIResponse($aiGeneration->content);
        } catch (\Exception $e) {
            Log::error("Failed to generate AI report: " . $e->getMessage());
            return $this->generateFallbackReport($data, $reportType);
        }
    }

    /**
     * Build report prompt for AI
     */
    private function buildReportPrompt(Organization $organization, array $data, string $reportType): string
    {
        $campaignCount = $data['campaigns']->count();
        $scheduledPosts = $data['scheduled_posts'];
        $publishedPosts = $data['published_posts'];
        $taskCount = $data['tasks']->count();
        $emailCampaignCount = $data['email_campaigns']->count();

        return "Generate a {$reportType} marketing performance report for {$organization->name}. 
        
        Key Metrics:
        - Active Campaigns: {$campaignCount}
        - Scheduled Posts: {$scheduledPosts}
        - Published Posts: {$publishedPosts}
        - Tasks Completed: {$taskCount}
        - Email Campaigns: {$emailCampaignCount}
        
        Provide:
        1. Executive Summary (2-3 sentences)
        2. Key Metrics (with percentage changes)
        3. Highlights (3-5 positive achievements)
        4. Recommendations (3-5 improvement suggestions)
        
        Format as JSON with keys: executive_summary, key_metrics, highlights, recommendations.";
    }

    /**
     * Parse AI response into structured report
     */
    private function parseAIResponse(string $aiResponse): array
    {
        $decoded = json_decode($aiResponse, true);

        if (json_last_error() === JSON_ERROR_NONE && isset($decoded['executive_summary'])) {
            return $decoded;
        }

        // Fallback parsing if JSON structure is different
        return $this->generateFallbackReport([], 'monthly');
    }

    /**
     * Generate fallback report when AI fails
     */
    private function generateFallbackReport(array $data, string $reportType): array
    {
        return [
            'executive_summary' => "This {$reportType} report provides an overview of marketing performance and key activities.",
            'key_metrics' => [
                ['metric' => 'Active Campaigns', 'value' => $data['campaigns']->count() ?? 0, 'change' => '0%'],
                ['metric' => 'Published Posts', 'value' => $data['published_posts'] ?? 0, 'change' => '0%'],
                ['metric' => 'Tasks Completed', 'value' => $data['tasks']->count() ?? 0, 'change' => '0%'],
            ],
            'highlights' => [
                'Continued campaign execution',
                'Regular content publishing',
                'Task management in progress',
            ],
            'recommendations' => [
                'Review campaign performance metrics',
                'Optimize content scheduling',
                'Enhance engagement strategies',
            ],
        ];
    }

    /**
     * Store report history
     */
    private function storeReportHistory(Agency $agency, Organization $organization, string $reportType, array $reportData): void
    {
        Report::create([
            'organization_id' => $organization->id,
            'name' => ucfirst($reportType) . " Report - {$organization->name}",
            'description' => "Agency-generated {$reportType} report for {$organization->name}",
            'type' => 'analytics',
            'data' => $reportData,
            'format' => 'pdf',
            'created_by' => auth()->id(),
            'config' => [
                'agency_id' => $agency->id,
                'report_type' => $reportType,
            ],
        ]);
    }
}

