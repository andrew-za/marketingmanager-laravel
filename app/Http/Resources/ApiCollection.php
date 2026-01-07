<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Base API collection with consistent response formatting and pagination
 */
class ApiCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     */
    public function with(Request $request): array
    {
        return [
            'success' => true,
            'message' => 'Data retrieved successfully',
            'meta' => [
                'pagination' => $this->getPaginationMeta($request),
            ],
        ];
    }

    /**
     * Get pagination metadata
     */
    protected function getPaginationMeta(Request $request): array
    {
        if (!$this->resource instanceof \Illuminate\Contracts\Pagination\Paginator) {
            return [];
        }

        return [
            'current_page' => $this->resource->currentPage(),
            'per_page' => $this->resource->perPage(),
            'total' => $this->resource->total(),
            'last_page' => $this->resource->lastPage(),
            'from' => $this->resource->firstItem(),
            'to' => $this->resource->lastItem(),
        ];
    }
}

