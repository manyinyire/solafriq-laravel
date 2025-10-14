<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;
use Exception;

class ImageOptimizationService
{
    protected ImageManager $manager;

    public function __construct()
    {
        // Initialize ImageManager with GD driver
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Upload and optimize an image
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param int|null $maxWidth
     * @param int|null $maxHeight
     * @param int $quality
     * @return string|null Path to uploaded file
     */
    public function uploadAndOptimize(
        UploadedFile $file,
        string $directory = 'uploads',
        ?int $maxWidth = 1920,
        ?int $maxHeight = 1080,
        int $quality = 90
    ): ?string {
        try {
            $storageDisk = $this->getDisk();
            
            // Create organized folder structure (year/month)
            $path = $directory . '/' . now()->format('FY') . '/';
            
            // Generate unique filename with WebP extension
            $filename = time() . '_' . Str::random(10) . '.webp';
            
            // Process and optimize the image
            $image = $this->manager->read($file->getPathname());
            
            // Resize if dimensions are specified
            if ($maxWidth || $maxHeight) {
                $image->scale(width: $maxWidth, height: $maxHeight);
            }
            
            // Encode to WebP
            $encodedImage = $image->toWebp($quality);
            
            // Store based on disk type
            if ($storageDisk === 's3') {
                Storage::disk('s3')->put($path . $filename, (string) $encodedImage);
                return $path . $filename;
            } else {
                // Store directly in public/storage for shared hosting compatibility
                $fullPath = public_path('storage/' . $path);
                
                // Create directory if it doesn't exist
                if (!file_exists($fullPath)) {
                    mkdir($fullPath, 0755, true);
                }
                
                // Save the image
                $encodedImage->save($fullPath . $filename);
                
                // Return the public path
                return $path . $filename;
            }
        } catch (Exception $e) {
            \Log::error('Image upload failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName()
            ]);
            
            return null;
        }
    }
    
    /**
     * Upload avatar with specific dimensions
     *
     * @param UploadedFile $file
     * @return string|null
     */
    public function uploadAvatar(UploadedFile $file): ?string
    {
        return $this->uploadAndOptimize(
            $file,
            'avatars',
            512,
            512,
            90
        );
    }
    
    /**
     * Upload logo with specific dimensions
     *
     * @param UploadedFile $file
     * @return string|null
     */
    public function uploadLogo(UploadedFile $file): ?string
    {
        return $this->uploadAndOptimize(
            $file,
            'logos',
            480,
            270,
            90
        );
    }
    
    /**
     * Upload product image
     *
     * @param UploadedFile $file
     * @return string|null
     */
    public function uploadProductImage(UploadedFile $file): ?string
    {
        return $this->uploadAndOptimize(
            $file,
            'products',
            1200,
            1200,
            85
        );
    }
    
    /**
     * Delete an image from storage
     *
     * @param string $path
     * @return bool
     */
    public function deleteImage(string $path): bool
    {
        try {
            $storageDisk = $this->getDisk();
            
            if ($storageDisk === 's3') {
                return Storage::disk('s3')->delete($path);
            } else {
                // Handle both storage and public paths
                if (str_starts_with($path, '/storage/')) {
                    $path = str_replace('/storage/', '', $path);
                }
                
                $fullPath = public_path('storage/' . $path);
                if (file_exists($fullPath)) {
                    return unlink($fullPath);
                }
                
                return false;
            }
        } catch (Exception $e) {
            \Log::error('Image deletion failed', [
                'error' => $e->getMessage(),
                'path' => $path
            ]);
            
            return false;
        }
    }
    
    /**
     * Create thumbnail from existing image
     *
     * @param string $sourcePath
     * @param int $width
     * @param int $height
     * @return string|null
     */
    public function createThumbnail(string $sourcePath, int $width = 150, int $height = 150): ?string
    {
        try {
            $storageDisk = $this->getDisk();
            
            // Get the source image path
            if ($storageDisk === 's3') {
                $imagePath = Storage::disk('s3')->path($sourcePath);
            } else {
                $imagePath = public_path('storage/' . $sourcePath);
            }
            
            // Create thumbnail
            $thumbnail = $this->manager->read($imagePath);
            $thumbnail->cover($width, $height);
            $encodedThumbnail = $thumbnail->toWebp(85);
            
            // Generate thumbnail path
            $pathInfo = pathinfo($sourcePath);
            $thumbnailPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_thumb.webp';
            
            // Store thumbnail
            if ($storageDisk === 's3') {
                Storage::disk('s3')->put($thumbnailPath, (string) $encodedThumbnail);
            } else {
                $fullPath = public_path('storage/' . $pathInfo['dirname']);
                if (!file_exists($fullPath)) {
                    mkdir($fullPath, 0755, true);
                }
                $encodedThumbnail->save(public_path('storage/' . $thumbnailPath));
            }
            
            return $thumbnailPath;
        } catch (Exception $e) {
            \Log::error('Thumbnail creation failed', [
                'error' => $e->getMessage(),
                'source' => $sourcePath
            ]);
            
            return null;
        }
    }
    
    /**
     * Get current storage disk
     *
     * @return string
     */
    protected function getDisk(): string
    {
        return config('filesystems.default', 'local');
    }
}
