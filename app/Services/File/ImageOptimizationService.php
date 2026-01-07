<?php

namespace App\Services\File;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Image optimization service for resizing and compressing images
 */
class ImageOptimizationService
{
    private ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Optimize image for web display
     */
    public function optimizeForWeb(string $filePath, string $disk = 'public', int $maxWidth = 1920, int $quality = 85): string
    {
        try {
            $image = $this->imageManager->read(Storage::disk($disk)->get($filePath));
            
            // Resize if larger than max width
            if ($image->width() > $maxWidth) {
                $image->scale(width: $maxWidth);
            }

            // Encode with quality setting
            $optimized = $image->toJpeg(quality: $quality);
            
            // Save optimized image
            Storage::disk($disk)->put($filePath, $optimized);

            return $filePath;
        } catch (\Exception $e) {
            Log::error("Failed to optimize image: " . $e->getMessage());
            return $filePath;
        }
    }

    /**
     * Create thumbnail from image
     */
    public function createThumbnail(string $filePath, string $disk = 'public', int $width = 300, int $height = 300): ?string
    {
        try {
            $image = $this->imageManager->read(Storage::disk($disk)->get($filePath));
            
            $image->cover($width, $height);
            
            $thumbnailPath = $this->getThumbnailPath($filePath);
            $thumbnail = $image->toJpeg(quality: 80);
            
            Storage::disk($disk)->put($thumbnailPath, $thumbnail);

            return $thumbnailPath;
        } catch (\Exception $e) {
            Log::error("Failed to create thumbnail: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Resize image to specific dimensions
     */
    public function resize(string $filePath, string $disk = 'public', int $width, int $height, bool $maintainAspectRatio = true): string
    {
        try {
            $image = $this->imageManager->read(Storage::disk($disk)->get($filePath));
            
            if ($maintainAspectRatio) {
                $image->scale(width: $width, height: $height);
            } else {
                $image->resize($width, $height);
            }

            $resized = $image->toJpeg(quality: 85);
            Storage::disk($disk)->put($filePath, $resized);

            return $filePath;
        } catch (\Exception $e) {
            Log::error("Failed to resize image: " . $e->getMessage());
            return $filePath;
        }
    }

    /**
     * Get thumbnail path from original file path
     */
    private function getThumbnailPath(string $filePath): string
    {
        $pathInfo = pathinfo($filePath);
        return $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
    }
}

