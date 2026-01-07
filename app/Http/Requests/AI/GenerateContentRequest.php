<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class GenerateContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $action = $this->route()->getActionMethod();

        return match($action) {
            'generateSocialMediaPost' => [
                'platform' => 'required|string|in:twitter,facebook,instagram,linkedin,tiktok,pinterest',
                'topic' => 'required|string|max:500',
                'brand_id' => 'nullable|exists:brands,id',
                'model' => 'nullable|string|in:gpt-4,gpt-4-turbo,gpt-3.5-turbo,gemini-pro',
                'temperature' => 'nullable|numeric|min:0|max:1',
                'tone' => 'nullable|string|max:100',
                'call_to_action' => 'nullable|string|max:200',
            ],
            'generatePressRelease' => [
                'topic' => 'required|string|max:500',
                'details' => 'nullable|array',
                'brand_id' => 'nullable|exists:brands,id',
                'model' => 'nullable|string|in:gpt-4,gpt-4-turbo,gpt-3.5-turbo,gemini-pro',
                'temperature' => 'nullable|numeric|min:0|max:1',
            ],
            'generateEmailTemplate' => [
                'purpose' => 'required|string|max:200',
                'audience' => 'required|string|max:200',
                'brand_id' => 'nullable|exists:brands,id',
                'model' => 'nullable|string|in:gpt-4,gpt-4-turbo,gpt-3.5-turbo,gemini-pro',
                'temperature' => 'nullable|numeric|min:0|max:1',
            ],
            'generateBlogPost' => [
                'topic' => 'required|string|max:500',
                'target_audience' => 'required|string|max:200',
                'word_count' => 'nullable|integer|min:300|max:5000',
                'brand_id' => 'nullable|exists:brands,id',
                'model' => 'nullable|string|in:gpt-4,gpt-4-turbo,gpt-3.5-turbo,gemini-pro',
                'temperature' => 'nullable|numeric|min:0|max:1',
            ],
            'generateAdCopy' => [
                'product' => 'required|string|max:200',
                'platform' => 'required|string|max:100',
                'objective' => 'required|string|max:200',
                'brand_id' => 'nullable|exists:brands,id',
                'model' => 'nullable|string|in:gpt-4,gpt-4-turbo,gpt-3.5-turbo,gemini-pro',
                'temperature' => 'nullable|numeric|min:0|max:1',
            ],
            'generateVariations' => [
                'base_content' => 'required|string|max:5000',
                'variation_count' => 'nullable|integer|min:2|max:10',
                'model' => 'nullable|string|in:gpt-4,gpt-4-turbo,gpt-3.5-turbo,gemini-pro',
                'temperature' => 'nullable|numeric|min:0|max:1',
            ],
            default => [],
        };
    }
}


