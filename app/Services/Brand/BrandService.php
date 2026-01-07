<?php

namespace App\Services\Brand;

use App\Models\Brand;
use App\Models\BrandAsset;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class BrandService
{
    public function createBrand(array $data, User $user): Brand
    {
        return DB::transaction(function () use ($data, $user) {
            $logoPath = null;
            if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
                $logoPath = $this->storeLogo($data['logo'], $user->primaryOrganization()->id);
            }

            $brand = Brand::create([
                'organization_id' => $user->primaryOrganization()->id,
                'name' => $data['name'],
                'summary' => $data['summary'] ?? null,
                'audience' => $data['audience'] ?? null,
                'guidelines' => $data['guidelines'] ?? null,
                'tone_of_voice' => $data['tone_of_voice'] ?? null,
                'keywords' => $data['keywords'] ?? [],
                'avoid_keywords' => $data['avoid_keywords'] ?? [],
                'logo' => $logoPath,
                'status' => $data['status'] ?? 'active',
                'business_model' => $data['business_model'] ?? null,
            ]);

            return $brand->load('organization');
        });
    }

    public function updateBrand(Brand $brand, array $data): Brand
    {
        return DB::transaction(function () use ($brand, $data) {
            if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
                if ($brand->logo) {
                    Storage::disk('public')->delete($brand->logo);
                }
                $data['logo'] = $this->storeLogo($data['logo'], $brand->organization_id);
            }

            $brand->update($data);

            return $brand->load('organization');
        });
    }

    public function deleteBrand(Brand $brand): bool
    {
        return DB::transaction(function () use ($brand) {
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }

            $brand->assets()->each(function ($asset) {
                Storage::disk('public')->delete($asset->url);
            });

            return $brand->delete();
        });
    }

    public function addAsset(Brand $brand, array $data, ?UploadedFile $file = null): BrandAsset
    {
        $url = null;
        if ($file) {
            $url = $this->storeAsset($file, $brand->organization_id, $data['type'] ?? 'other');
        } elseif (isset($data['url'])) {
            $url = $data['url'];
        }

        return BrandAsset::create([
            'brand_id' => $brand->id,
            'name' => $data['name'],
            'type' => $data['type'] ?? 'other',
            'url' => $url,
            'tags' => $data['tags'] ?? [],
        ]);
    }

    public function updateAsset(BrandAsset $asset, array $data, ?UploadedFile $file = null): BrandAsset
    {
        if ($file) {
            if ($asset->url && Storage::disk('public')->exists($asset->url)) {
                Storage::disk('public')->delete($asset->url);
            }
            $data['url'] = $this->storeAsset($file, $asset->brand->organization_id, $data['type'] ?? $asset->type);
        }

        $asset->update($data);

        return $asset->fresh();
    }

    public function removeAsset(BrandAsset $asset): bool
    {
        if ($asset->url && Storage::disk('public')->exists($asset->url)) {
            Storage::disk('public')->delete($asset->url);
        }

        return $asset->delete();
    }

    /**
     * Get brand assets grouped by type
     */
    public function getAssetsGroupedByType(Brand $brand): array
    {
        $assets = $brand->assets()
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('type')
            ->map(function ($group) {
                return $group->map(function ($asset) {
                    return [
                        'id' => $asset->id,
                        'name' => $asset->name,
                        'type' => $asset->type,
                        'url' => $asset->url ? Storage::url($asset->url) : null,
                        'tags' => $asset->tags,
                        'created_at' => $asset->created_at->toIso8601String(),
                    ];
                })->values();
            })
            ->toArray();

        return [
            'logo' => $assets['logo'] ?? [],
            'image' => $assets['image'] ?? [],
            'font' => $assets['font'] ?? [],
            'color' => $assets['color'] ?? [],
            'other' => $assets['other'] ?? [],
        ];
    }

    /**
     * Get brand guidelines with formatted data
     */
    public function getBrandGuidelines(Brand $brand): array
    {
        return [
            'guidelines' => $brand->guidelines,
            'tone_of_voice' => $brand->tone_of_voice,
            'keywords' => $brand->keywords ?? [],
            'avoid_keywords' => $brand->avoid_keywords ?? [],
            'audience' => $brand->audience,
            'summary' => $brand->summary,
            'logo_url' => $brand->logo ? Storage::url($brand->logo) : null,
        ];
    }

    private function storeLogo(UploadedFile $file, int $organizationId): string
    {
        return $file->store("brands/{$organizationId}/logos", 'public');
    }

    private function storeAsset(UploadedFile $file, int $organizationId, string $type): string
    {
        $folder = match($type) {
            'logo' => 'logos',
            'image' => 'images',
            'font' => 'fonts',
            default => 'other',
        };

        return $file->store("brands/{$organizationId}/{$folder}", 'public');
    }
}

