<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\GenerateImageRequest;
use App\Models\GeneratedImage;
use App\Services\AI\ImageGenerationService;
use Illuminate\Http\JsonResponse;

class AiImageController extends Controller
{
    public function __construct(
        private ImageGenerationService $imageService
    ) {}

    public function generateImage(GenerateImageRequest $request): JsonResponse
    {
        $organization = $request->user()->primaryOrganization();

        $image = $this->imageService->generateImage(
            $organization,
            $request->user(),
            $request->prompt,
            $request->style ?? 'realistic',
            $request->only(['size'])
        );

        return response()->json([
            'success' => true,
            'data' => $image->load('user'),
        ]);
    }

    public function optimizeForPlatform(GenerateImageRequest $request, GeneratedImage $image): JsonResponse
    {
        $optimized = $this->imageService->optimizeForPlatform(
            $image,
            $request->platform
        );

        return response()->json([
            'success' => true,
            'data' => $optimized,
        ]);
    }

    public function addToLibrary(GenerateImageRequest $request, GeneratedImage $image): JsonResponse
    {
        $libraryItem = $this->imageService->addToLibrary(
            $image,
            $request->name,
            $request->description,
            $request->tags ?? []
        );

        return response()->json([
            'success' => true,
            'data' => $libraryItem,
        ]);
    }
}


