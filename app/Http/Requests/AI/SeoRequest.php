<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class SeoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $action = $this->route()->getActionMethod();

        return match($action) {
            'researchKeyword' => [
                'keyword' => 'required|string|max:255',
            ],
            'analyzeContent' => [
                'url' => 'required|url|max:500',
                'content' => 'required|string|max:50000',
            ],
            'generateMetaTags' => [
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:500',
                'keywords' => 'nullable|array',
                'keywords.*' => 'string|max:50',
            ],
            'generateSitemap' => [
                'urls' => 'required|array|min:1',
                'urls.*.loc' => 'required|url',
                'urls.*.lastmod' => 'nullable|date',
                'urls.*.changefreq' => 'nullable|string|in:always,hourly,daily,weekly,monthly,yearly,never',
                'urls.*.priority' => 'nullable|numeric|min:0|max:1',
            ],
            'analyzeCompetitor' => [
                'competitor_url' => 'required|url|max:500',
            ],
            default => [],
        };
    }
}


