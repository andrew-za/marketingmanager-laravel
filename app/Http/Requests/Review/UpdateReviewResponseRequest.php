<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'response' => ['sometimes', 'required', 'string', 'max:5000'],
            'response_type' => ['sometimes', 'required', 'in:public,private'],
        ];
    }
}

