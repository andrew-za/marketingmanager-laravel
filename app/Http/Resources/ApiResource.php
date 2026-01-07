<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Base API resource with consistent response formatting
 */
class ApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }

    /**
     * Get additional data that should be returned with the resource array.
     */
    public function with(Request $request): array
    {
        return [
            'success' => true,
            'message' => $this->getMessage($request),
        ];
    }

    /**
     * Get the message for the response
     */
    protected function getMessage(Request $request): string
    {
        return 'Operation successful';
    }
}

