<?php

namespace App\Services\Brand;

use App\Models\BrandNameSuggestion;
use App\Models\Organization;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BrandNameGeneratorService
{
    private array $socialPlatforms = ['twitter', 'instagram', 'facebook', 'linkedin', 'tiktok'];

    public function generateSuggestions(Organization $organization, array $keywords, int $count = 10): array
    {
        $suggestions = [];
        
        for ($i = 0; $i < $count; $i++) {
            $name = $this->generateName($keywords);
            $domainAvailable = $this->checkDomainAvailability($name);
            $socialHandles = $this->checkSocialHandles($name);

            $suggestion = BrandNameSuggestion::create([
                'organization_id' => $organization->id,
                'suggested_name' => $name,
                'description' => $this->generateDescription($name, $keywords),
                'domain_available' => $domainAvailable ? 'yes' : 'no',
                'social_handles' => $socialHandles,
                'status' => 'pending',
            ]);

            $suggestions[] = $suggestion;
        }

        return $suggestions;
    }

    public function checkDomainAvailability(string $domain): bool
    {
        try {
            $domain = Str::slug($domain);
            $response = Http::timeout(5)->get("https://api.domainsdb.info/v1/domains/search", [
                'domain' => $domain . '.com',
                'zone' => 'com',
            ]);

            $data = $response->json();
            return empty($data['domains'] ?? []);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function checkSocialHandles(string $name): array
    {
        $handles = [];
        $slug = Str::slug($name);

        foreach ($this->socialPlatforms as $platform) {
            $handles[$platform] = [
                'handle' => $slug,
                'available' => $this->checkPlatformHandle($platform, $slug),
            ];
        }

        return $handles;
    }

    private function checkPlatformHandle(string $platform, string $handle): bool
    {
        try {
            $response = Http::timeout(5)->get("https://{$platform}.com/{$handle}");
            return $response->status() === 404;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function generateName(array $keywords): string
    {
        if (empty($keywords)) {
            return Str::random(8);
        }

        $keyword = $keywords[array_rand($keywords)];
        $suffixes = ['ly', 'ify', 'io', 'app', 'hub', 'pro', 'tech', 'lab', 'works'];
        $suffix = $suffixes[array_rand($suffixes)];

        return Str::title($keyword) . $suffix;
    }

    private function generateDescription(string $name, array $keywords): string
    {
        $keywordList = implode(', ', array_slice($keywords, 0, 3));
        return "A modern brand name combining {$keywordList} concepts with a contemporary feel.";
    }
}

