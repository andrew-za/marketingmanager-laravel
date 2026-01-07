<?php

namespace App\Http\Requests\Survey;

use Illuminate\Foundation\Http\FormRequest;

class CreateSurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Survey::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,active,closed'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'settings' => ['nullable', 'array'],
            'questions' => ['nullable', 'array'],
            'questions.*.question' => ['required_with:questions', 'string'],
            'questions.*.type' => ['required_with:questions', 'in:text,textarea,radio,checkbox,select,rating,date'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.is_required' => ['nullable', 'boolean'],
            'questions.*.order' => ['nullable', 'integer'],
        ];
    }
}

