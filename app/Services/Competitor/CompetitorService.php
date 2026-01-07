<?php

namespace App\Services\Competitor;

use App\Models\Competitor;
use App\Models\CompetitorAnalysis;
use App\Models\CompetitorPost;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CompetitorService
{
    public function createCompetitor(array $data, User $user): Competitor
    {
        return DB::transaction(function () use ($data, $user) {
            return Competitor::create([
                ...$data,
                'organization_id' => $user->primaryOrganization()->id,
            ]);
        });
    }

    public function updateCompetitor(Competitor $competitor, array $data): Competitor
    {
        return DB::transaction(function () use ($competitor, $data) {
            $competitor->update($data);
            return $competitor->fresh();
        });
    }

    public function deleteCompetitor(Competitor $competitor): bool
    {
        return DB::transaction(function () use ($competitor) {
            return $competitor->delete();
        });
    }

    public function createAnalysis(Competitor $competitor, array $data): CompetitorAnalysis
    {
        return DB::transaction(function () use ($competitor, $data) {
            return CompetitorAnalysis::create([
                'competitor_id' => $competitor->id,
                'analysis_type' => $data['analysis_type'],
                'metrics' => $data['metrics'] ?? [],
                'insights' => $data['insights'] ?? null,
                'analyzed_at' => now(),
            ]);
        });
    }

    public function trackPost(Competitor $competitor, array $data): CompetitorPost
    {
        return DB::transaction(function () use ($competitor, $data) {
            return CompetitorPost::create([
                'competitor_id' => $competitor->id,
                'platform' => $data['platform'],
                'platform_post_id' => $data['platform_post_id'],
                'content' => $data['content'],
                'published_at' => $data['published_at'] ?? now(),
                'engagement_metrics' => $data['engagement_metrics'] ?? [],
                'metadata' => $data['metadata'] ?? [],
            ]);
        });
    }

    public function compareCompetitors(array $competitorIds, array $metrics): array
    {
        $comparison = [];
        
        foreach ($competitorIds as $competitorId) {
            $competitor = Competitor::find($competitorId);
            if (!$competitor) {
                continue;
            }

            $comparison[$competitorId] = [
                'name' => $competitor->name,
                'metrics' => [],
            ];

            foreach ($metrics as $metric) {
                $latestAnalysis = CompetitorAnalysis::where('competitor_id', $competitorId)
                    ->where('metrics', 'like', "%\"{$metric}\"%")
                    ->latest('analyzed_at')
                    ->first();

                if ($latestAnalysis && isset($latestAnalysis->metrics[$metric])) {
                    $comparison[$competitorId]['metrics'][$metric] = $latestAnalysis->metrics[$metric];
                }
            }
        }

        return $comparison;
    }

    public function generateIntelligenceReport(array $competitorIds, array $options = []): array
    {
        $report = [
            'generated_at' => now()->toIso8601String(),
            'competitors' => [],
            'summary' => [],
            'insights' => [],
        ];

        foreach ($competitorIds as $competitorId) {
            $competitor = Competitor::with(['analyses', 'posts'])->find($competitorId);
            if (!$competitor) {
                continue;
            }

            $recentPosts = $competitor->posts()
                ->where('published_at', '>=', now()->subDays($options['days'] ?? 30))
                ->get();

            $recentAnalyses = $competitor->analyses()
                ->where('analyzed_at', '>=', now()->subDays($options['days'] ?? 30))
                ->get();

            $report['competitors'][$competitorId] = [
                'name' => $competitor->name,
                'post_count' => $recentPosts->count(),
                'average_engagement' => $recentPosts->avg('engagement_metrics.likes') ?? 0,
                'analysis_count' => $recentAnalyses->count(),
                'key_insights' => $recentAnalyses->pluck('insights')->filter()->toArray(),
            ];
        }

        $report['summary'] = [
            'total_competitors' => count($report['competitors']),
            'total_posts' => array_sum(array_column($report['competitors'], 'post_count')),
            'average_engagement' => array_sum(array_column($report['competitors'], 'average_engagement')) / count($report['competitors']),
        ];

        return $report;
    }
}

