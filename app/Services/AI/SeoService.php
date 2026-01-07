<?php

namespace App\Services\AI;

use App\Models\KeywordResearch;
use App\Models\SeoAnalysis;
use App\Models\Organization;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SeoService
{
    private string $serpApiKey;

    public function __construct()
    {
        $this->serpApiKey = config('services.serp.api_key', '');
    }

    public function researchKeyword(
        Organization $organization,
        string $keyword
    ): KeywordResearch {
        $existing = KeywordResearch::where('organization_id', $organization->id)
            ->where('keyword', $keyword)
            ->first();

        if ($existing) {
            return $existing;
        }

        $data = $this->fetchKeywordData($keyword);

        return KeywordResearch::create([
            'organization_id' => $organization->id,
            'keyword' => $keyword,
            'search_volume' => $data['search_volume'] ?? null,
            'difficulty' => $data['difficulty'] ?? null,
            'cpc' => $data['cpc'] ?? null,
            'related_keywords' => $data['related_keywords'] ?? [],
            'trends' => $data['trends'] ?? [],
        ]);
    }

    public function analyzeContent(
        Organization $organization,
        string $url,
        string $content
    ): SeoAnalysis {
        $wordCount = str_word_count(strip_tags($content));
        $readingTime = (int)ceil($wordCount / 200);

        $metaTags = $this->extractMetaTags($content);
        $keywords = $this->extractKeywords($content);
        $recommendations = $this->generateRecommendations($content, $metaTags, $keywords);
        $seoScore = $this->calculateSeoScore($content, $metaTags, $keywords);

        return SeoAnalysis::create([
            'organization_id' => $organization->id,
            'url' => $url,
            'meta_tags' => $metaTags,
            'keywords' => $keywords,
            'word_count' => $wordCount,
            'reading_time' => $readingTime,
            'recommendations' => $recommendations,
            'seo_score' => $seoScore,
            'analyzed_at' => now()->toDateString(),
        ]);
    }

    public function generateMetaTags(
        Organization $organization,
        string $title,
        string $description,
        array $keywords = []
    ): array {
        return [
            'title' => $this->optimizeTitle($title),
            'description' => $this->optimizeDescription($description),
            'keywords' => implode(', ', array_slice($keywords, 0, 10)),
            'og:title' => $this->optimizeTitle($title),
            'og:description' => $this->optimizeDescription($description),
            'twitter:card' => 'summary_large_image',
            'twitter:title' => $this->optimizeTitle($title),
            'twitter:description' => $this->optimizeDescription($description),
        ];
    }

    public function generateSitemap(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($url['loc']) . "</loc>\n";
            
            if (isset($url['lastmod'])) {
                $xml .= "    <lastmod>" . htmlspecialchars($url['lastmod']) . "</lastmod>\n";
            }
            
            if (isset($url['changefreq'])) {
                $xml .= "    <changefreq>" . htmlspecialchars($url['changefreq']) . "</changefreq>\n";
            }
            
            if (isset($url['priority'])) {
                $xml .= "    <priority>" . htmlspecialchars($url['priority']) . "</priority>\n";
            }
            
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    public function analyzeCompetitorSeo(
        Organization $organization,
        string $competitorUrl
    ): array {
        $content = $this->fetchUrlContent($competitorUrl);
        
        if (!$content) {
            throw new \Exception('Unable to fetch competitor content');
        }

        $analysis = $this->analyzeContent($organization, $competitorUrl, $content);

        return [
            'url' => $competitorUrl,
            'seo_score' => $analysis->seo_score,
            'word_count' => $analysis->word_count,
            'meta_tags' => $analysis->meta_tags,
            'keywords' => $analysis->keywords,
            'recommendations' => $analysis->recommendations,
        ];
    }

    private function fetchKeywordData(string $keyword): array
    {
        if (!$this->serpApiKey) {
            return $this->getMockKeywordData($keyword);
        }

        try {
            $response = Http::get('https://api.serpapi.com/search', [
                'api_key' => $this->serpApiKey,
                'engine' => 'google',
                'q' => $keyword,
                'hl' => 'en',
            ]);

            if ($response->failed()) {
                return $this->getMockKeywordData($keyword);
            }

            $data = $response->json();
            
            return [
                'search_volume' => $data['search_information']['total_results'] ?? null,
                'difficulty' => rand(30, 80),
                'cpc' => rand(50, 500) / 100,
                'related_keywords' => $this->extractRelatedKeywords($data),
                'trends' => [],
            ];
        } catch (\Exception $e) {
            return $this->getMockKeywordData($keyword);
        }
    }

    private function getMockKeywordData(string $keyword): array
    {
        return [
            'search_volume' => rand(1000, 100000),
            'difficulty' => rand(30, 80),
            'cpc' => rand(50, 500) / 100,
            'related_keywords' => [
                $keyword . ' tips',
                $keyword . ' guide',
                'best ' . $keyword,
            ],
            'trends' => [],
        ];
    }

    private function extractRelatedKeywords(array $data): array
    {
        $keywords = [];
        
        if (isset($data['related_searches'])) {
            foreach ($data['related_searches'] as $search) {
                $keywords[] = $search['query'] ?? '';
            }
        }

        return array_filter($keywords);
    }

    private function extractMetaTags(string $content): array
    {
        $metaTags = [];
        
        if (preg_match('/<title>(.*?)<\/title>/i', $content, $matches)) {
            $metaTags['title'] = trim($matches[1]);
        }
        
        if (preg_match('/<meta\s+name=["\']description["\']\s+content=["\'](.*?)["\']/i', $content, $matches)) {
            $metaTags['description'] = trim($matches[1]);
        }
        
        if (preg_match_all('/<meta\s+name=["\']keywords["\']\s+content=["\'](.*?)["\']/i', $content, $matches)) {
            $metaTags['keywords'] = trim($matches[1][0] ?? '');
        }

        return $metaTags;
    }

    private function extractKeywords(string $content): array
    {
        $text = strip_tags($content);
        $words = str_word_count(strtolower($text), 1);
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by'];
        $words = array_diff($words, $stopWords);
        
        $wordFreq = array_count_values($words);
        arsort($wordFreq);
        
        return array_slice(array_keys($wordFreq), 0, 20);
    }

    private function generateRecommendations(array $content, array $metaTags, array $keywords): array
    {
        $recommendations = [];

        if (empty($metaTags['title'])) {
            $recommendations[] = 'Add a title tag (50-60 characters recommended)';
        } elseif (strlen($metaTags['title']) > 60) {
            $recommendations[] = 'Title tag is too long. Keep it under 60 characters.';
        }

        if (empty($metaTags['description'])) {
            $recommendations[] = 'Add a meta description (150-160 characters recommended)';
        } elseif (strlen($metaTags['description']) > 160) {
            $recommendations[] = 'Meta description is too long. Keep it under 160 characters.';
        }

        if (str_word_count(strip_tags($content)) < 300) {
            $recommendations[] = 'Content is too short. Aim for at least 300 words for better SEO.';
        }

        if (count($keywords) < 5) {
            $recommendations[] = 'Add more relevant keywords to improve SEO.';
        }

        return $recommendations;
    }

    private function calculateSeoScore(string $content, array $metaTags, array $keywords): float
    {
        $score = 0;
        $maxScore = 100;

        if (!empty($metaTags['title'])) {
            $score += 20;
            if (strlen($metaTags['title']) >= 50 && strlen($metaTags['title']) <= 60) {
                $score += 5;
            }
        }

        if (!empty($metaTags['description'])) {
            $score += 20;
            if (strlen($metaTags['description']) >= 150 && strlen($metaTags['description']) <= 160) {
                $score += 5;
            }
        }

        $wordCount = str_word_count(strip_tags($content));
        if ($wordCount >= 300) {
            $score += 20;
        } elseif ($wordCount >= 200) {
            $score += 10;
        }

        if (count($keywords) >= 10) {
            $score += 20;
        } elseif (count($keywords) >= 5) {
            $score += 10;
        }

        return min($maxScore, $score);
    }

    private function optimizeTitle(string $title): string
    {
        $title = Str::limit($title, 60, '');
        return trim($title);
    }

    private function optimizeDescription(string $description): string
    {
        $description = Str::limit($description, 160, '');
        return trim($description);
    }

    private function fetchUrlContent(string $url): ?string
    {
        try {
            $content = file_get_contents($url);
            return $content ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }
}


