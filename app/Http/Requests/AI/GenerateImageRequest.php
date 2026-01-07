<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class GenerateImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $action = $this->route()->getActionMethod();

        return match($action) {
            'generateImage' => [
                'prompt' => 'required|string|max:1000',
                'style' => 'nullable|string|in:realistic,artistic,minimalist,vintage,modern',
                'size' => 'nullable|string|in:1024x1024,1792x1024,1024x1792',
            ],
            'optimizeForPlatform' => [
                'platform' => 'required|string|in:facebook,instagram,twitter,linkedin,pinterest',
            ],
            'addToLibrary' => [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:50',
            ],
            default => [],
        };
    }
}


