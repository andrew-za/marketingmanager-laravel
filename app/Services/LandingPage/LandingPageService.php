<?php

namespace App\Services\LandingPage;

use App\Models\LandingPage;
use App\Models\LandingPageVariant;
use App\Models\LandingPageTemplate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LandingPageService
{
    public function createLandingPage(array $data, User $user): LandingPage
    {
        return DB::transaction(function () use ($data, $user) {
            if (!isset($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            return LandingPage::create([
                ...$data,
                'organization_id' => $user->primaryOrganization()->id,
                'created_by' => $user->id,
            ]);
        });
    }

    public function updateLandingPage(LandingPage $landingPage, array $data): LandingPage
    {
        return DB::transaction(function () use ($landingPage, $data) {
            if (isset($data['name']) && !isset($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            $landingPage->update($data);
            return $landingPage->fresh();
        });
    }

    public function deleteLandingPage(LandingPage $landingPage): bool
    {
        return DB::transaction(function () use ($landingPage) {
            return $landingPage->delete();
        });
    }

    public function publishLandingPage(LandingPage $landingPage): LandingPage
    {
        return DB::transaction(function () use ($landingPage) {
            $landingPage->update(['status' => 'published', 'is_active' => true]);
            return $landingPage->fresh();
        });
    }

    public function createVariant(LandingPage $landingPage, array $data): LandingPageVariant
    {
        return DB::transaction(function () use ($landingPage, $data) {
            return LandingPageVariant::create([
                'landing_page_id' => $landingPage->id,
                'name' => $data['name'],
                'html_content' => $data['html_content'] ?? null,
                'page_data' => $data['page_data'] ?? [],
                'traffic_percentage' => $data['traffic_percentage'] ?? 50,
            ]);
        });
    }

    public function updateVariant(LandingPageVariant $variant, array $data): LandingPageVariant
    {
        return DB::transaction(function () use ($variant, $data) {
            $variant->update($data);
            return $variant->fresh();
        });
    }

    public function setWinnerVariant(LandingPageVariant $variant): LandingPageVariant
    {
        return DB::transaction(function () use ($variant) {
            LandingPageVariant::where('landing_page_id', $variant->landing_page_id)
                ->update(['is_winner' => false]);

            $variant->update(['is_winner' => true]);
            return $variant->fresh();
        });
    }

    public function createTemplate(array $data, ?User $user = null): LandingPageTemplate
    {
        return LandingPageTemplate::create([
            'organization_id' => $user?->primaryOrganization()?->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'template_content' => $data['template_content'],
            'template_data' => $data['template_data'] ?? [],
            'category' => $data['category'] ?? null,
            'preview_images' => $data['preview_images'] ?? [],
            'is_public' => $data['is_public'] ?? false,
            'created_by' => $user?->id,
        ]);
    }

    public function generateFromTemplate(LandingPageTemplate $template, array $data, User $user): LandingPage
    {
        return $this->createLandingPage([
            'name' => $data['name'] ?? $template->name,
            'description' => $data['description'] ?? $template->description,
            'html_content' => $template->template_content,
            'template_data' => array_merge($template->template_data ?? [], $data['template_data'] ?? []),
            'status' => 'draft',
        ], $user);
    }

    public function updateSeoSettings(LandingPage $landingPage, array $seoSettings): LandingPage
    {
        $landingPage->update(['seo_settings' => $seoSettings]);
        return $landingPage->fresh();
    }

    public function updateCustomDomain(LandingPage $landingPage, ?string $domain): LandingPage
    {
        $landingPage->update(['custom_domain' => $domain]);
        return $landingPage->fresh();
    }
}

