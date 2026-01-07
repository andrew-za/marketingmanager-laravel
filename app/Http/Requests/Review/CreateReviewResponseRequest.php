<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class CreateReviewResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'response' => ['required', 'string', 'max:5000'],
            'response_type' => ['required', 'in:public,private'],
        ];
    }
}

