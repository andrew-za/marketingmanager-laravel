<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'brand' => $this->whenLoaded('brand', function () {
                return [
                    'id' => $this->brand->id,
                    'name' => $this->brand->name,
                ];
            }),
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'price' => $this->price,
            'stock' => $this->stock,
            'status' => $this->status,
            'description' => $this->description,
            'image' => $this->image,
            'metadata' => $this->metadata,
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'variants_count' => $this->when(isset($this->variants_count), $this->variants_count),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}

