<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Exception;

class ImageOptimizationService
{
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
            $image = Image::make($file)
                ->encode('webp', $quality);
            
            // Resize if dimensions are specified
            if ($maxWidth || $maxHeight) {
                $image->resize($maxWidth, $maxHeight, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize(); // Prevent upsizing
                });
            }
            
            // Store based on disk type
            if ($storageDisk === 's3') {
                Storage::disk('s3')->put($path . $filename, (string) $image->encode());
                return $path . $filename;
            } else {
                // Store locally in public disk
                $fullPath = storage_path('app/public/' . $path);
                
                // Create directory if it doesn't exist
                if (!file_exists($fullPath)) {
                    mkdir($fullPath, 0755, true);
                }
                
                // Save the image
                $image->save($fullPath . $filename);
                
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
                } elseif (str_starts_with($path, '/uploads/')) {
                    $fullPath = public_path($path);
                    if (file_exists($fullPath)) {
                        return unlink($fullPath);
                    }
                    return false;
                }
                
                return Storage::disk('public')->delete($path);
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
            
            // Get the source image
            if ($storageDisk === 's3') {
                $imageContent = Storage::disk('s3')->get($sourcePath);
            } else {
                $imageContent = Storage::disk('public')->get($sourcePath);
            }
            
            // Create thumbnail
            $thumbnail = Image::make($imageContent)
                ->fit($width, $height)
                ->encode('webp', 85);
            
            // Generate thumbnail path
            $pathInfo = pathinfo($sourcePath);
            $thumbnailPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_thumb.webp';
            
            // Store thumbnail
            if ($storageDisk === 's3') {
                Storage::disk('s3')->put($thumbnailPath, (string) $thumbnail->encode());
            } else {
                $fullPath = storage_path('app/public/' . $pathInfo['dirname']);
                if (!file_exists($fullPath)) {
                    mkdir($fullPath, 0755, true);
                }
                $thumbnail->save(storage_path('app/public/' . $thumbnailPath));
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
