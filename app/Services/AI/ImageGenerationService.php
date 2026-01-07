<?php

namespace App\Services\AI;

use App\Models\GeneratedImage;
use App\Models\ImageLibrary;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageGenerationService
{
    private array $stylePresets = [
        'realistic' => ['style' => 'vivid', 'quality' => 'hd'],
        'artistic' => ['style' => 'vivid', 'quality' => 'standard'],
        'minimalist' => ['style' => 'vivid', 'quality' => 'standard'],
        'vintage' => ['style' => 'vivid', 'quality' => 'standard'],
        'modern' => ['style' => 'vivid', 'quality' => 'hd'],
    ];

    private array $platformSizes = [
        'facebook' => ['width' => 1200, 'height' => 630],
        'instagram' => ['width' => 1080, 'height' => 1080],
        'twitter' => ['width' => 1200, 'height' => 675],
        'linkedin' => ['width' => 1200, 'height' => 627],
        'pinterest' => ['width' => 1000, 'height' => 1500],
    ];

    public function __construct(
        private AiProviderService $providerService,
        private RateLimitingService $rateLimitingService,
        private AiUsageTrackingService $usageTrackingService
    ) {}

    public function generateImage(
        Organization $organization,
        User $user,
        string $prompt,
        string $style = 'realistic',
        array $options = []
    ): GeneratedImage {
        $this->rateLimitingService->checkRateLimit($organization, 'ai_image_generation');

        $provider = $this->providerService->getProvider('openai');
        $styleOptions = $this->stylePresets[$style] ?? $this->stylePresets['realistic'];
        
        $size = $options['size'] ?? '1024x1024';
        $generationOptions = array_merge($styleOptions, [
            'size' => $size,
            'prompt' => $this->enhancePrompt($prompt, $style),
        ]);

        try {
            $result = $provider->generateImage($prompt, $generationOptions);
            
            $imagePath = $this->downloadAndStoreImage($result['image_url'], $organization, $user);
            
            $dimensions = $this->getImageDimensions($imagePath);
            
            $generatedImage = GeneratedImage::create([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'provider' => 'openai',
                'model' => 'dall-e-3',
                'prompt' => $prompt,
                'image_url' => $result['image_url'],
                'image_path' => $imagePath,
                'width' => $dimensions['width'],
                'height' => $dimensions['height'],
                'cost' => $result['cost'],
                'metadata' => [
                    'style' => $style,
                    'size' => $size,
                ],
            ]);

            $this->rateLimitingService->incrementUsage($organization, 'ai_image_generation');

            return $generatedImage;
        } catch (\Exception $e) {
            throw new \Exception('Image generation failed: ' . $e->getMessage());
        }
    }

    public function optimizeForPlatform(
        GeneratedImage $image,
        string $platform
    ): GeneratedImage {
        if (!isset($this->platformSizes[$platform])) {
            return $image;
        }

        $targetSize = $this->platformSizes[$platform];
        $optimizedPath = $this->resizeImage(
            $image->image_path,
            $targetSize['width'],
            $targetSize['height']
        );

        return GeneratedImage::create([
            'organization_id' => $image->organization_id,
            'user_id' => $image->user_id,
            'provider' => $image->provider,
            'model' => $image->model,
            'prompt' => $image->prompt . " (optimized for {$platform})",
            'image_url' => Storage::url($optimizedPath),
            'image_path' => $optimizedPath,
            'width' => $targetSize['width'],
            'height' => $targetSize['height'],
            'cost' => 0,
            'metadata' => array_merge($image->metadata ?? [], [
                'platform' => $platform,
                'original_image_id' => $image->id,
            ]),
        ]);
    }

    public function addToLibrary(
        GeneratedImage $image,
        string $name,
        ?string $description = null,
        array $tags = []
    ): ImageLibrary {
        return ImageLibrary::create([
            'organization_id' => $image->organization_id,
            'name' => $name,
            'description' => $description,
            'file_path' => $image->image_path,
            'file_url' => $image->image_url,
            'mime_type' => 'image/png',
            'file_size' => Storage::size($image->image_path),
            'width' => $image->width,
            'height' => $image->height,
            'tags' => $tags,
            'source' => 'generated',
            'uploaded_by' => $image->user_id,
        ]);
    }

    private function downloadAndStoreImage(string $url, Organization $organization, User $user): string
    {
        $imageData = file_get_contents($url);
        $filename = 'ai-generated/' . $organization->id . '/' . uniqid() . '.png';
        
        Storage::disk('local')->put($filename, $imageData);
        
        return $filename;
    }

    private function resizeImage(string $imagePath, int $width, int $height): string
    {
        $manager = new ImageManager(new Driver());
        $fullPath = Storage::disk('local')->path($imagePath);
        $image = $manager->read($fullPath);
        
        $image->cover($width, $height);
        
        $newPath = str_replace('.png', '_' . $width . 'x' . $height . '.png', $imagePath);
        $newFullPath = Storage::disk('local')->path($newPath);
        $image->save($newFullPath);
        
        return $newPath;
    }

    private function getImageDimensions(string $imagePath): array
    {
        $manager = new ImageManager(new Driver());
        $fullPath = Storage::disk('local')->path($imagePath);
        $image = $manager->read($fullPath);
        
        return [
            'width' => $image->width(),
            'height' => $image->height(),
        ];
    }

    private function enhancePrompt(string $prompt, string $style): string
    {
        $enhancements = [
            'realistic' => 'photorealistic, high quality, detailed',
            'artistic' => 'artistic style, creative, visually striking',
            'minimalist' => 'minimalist design, clean, simple',
            'vintage' => 'vintage style, retro, classic',
            'modern' => 'modern design, contemporary, sleek',
        ];

        $enhancement = $enhancements[$style] ?? '';
        
        return $enhancement ? "{$prompt}, {$enhancement}" : $prompt;
    }
}

