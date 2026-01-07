<?php

namespace App\Http\Resources\Brand;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BrandAssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $url = $this->url;
        if ($url && !filter_var($url, FILTER_VALIDATE_URL)) {
            $url = Storage::url($url);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'url' => $url,
            'tags' => $this->tags ?? [],
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}

