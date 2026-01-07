<?php

namespace App\Http\Resources\Survey;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'start_date' => $this->start_date?->toIso8601String(),
            'end_date' => $this->end_date?->toIso8601String(),
            'response_count' => $this->response_count,
            'settings' => $this->settings,
            'created_by' => [
                'id' => $this->creator->id ?? null,
                'name' => $this->creator->name ?? null,
            ],
            'questions' => $this->whenLoaded('questions'),
            'responses' => $this->whenLoaded('responses'),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}

