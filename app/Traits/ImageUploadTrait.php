<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait ImageUploadTrait
{
    /**
     * Upload an image and return the path
     *
     * @param UploadedFile $image
     * @param string $directory
     * @param string|null $oldImagePath
     * @return string
     */
    public function uploadImage(UploadedFile $image, string $directory = 'images', string $oldImagePath = null): string
    {
        // Delete old image if exists
        if ($oldImagePath) {
            $this->deleteImage($oldImagePath);
        }

        // Generate unique filename
        $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
        
        // Store image in public disk
        $path = $image->storeAs($directory, $filename, 'public');
        
        return $path;
    }

    /**
     * Update image - upload new and delete old
     *
     * @param UploadedFile|null $newImage
     * @param string|null $oldImagePath
     * @param string $directory
     * @return string|null
     */
    public function updateImage(UploadedFile $newImage = null, string $oldImagePath = null, string $directory = 'images'): ?string
    {
        // If no new image provided, keep the old one
        if (!$newImage) {
            return $oldImagePath;
        }

        // Upload new image and delete old
        return $this->uploadImage($newImage, $directory, $oldImagePath);
    }

    /**
     * Delete an image from storage
     *
     * @param string|null $imagePath
     * @return bool
     */
    public function deleteImage(string $imagePath = null): bool
    {
        if (!$imagePath) {
            return false;
        }

        // Delete from public disk
        if (Storage::disk('public')->exists($imagePath)) {
            return Storage::disk('public')->delete($imagePath);
        }

        return false;
    }

    /**
     * Get full URL for image
     *
     * @param string|null $imagePath
     * @return string|null
     */
    public function getImageUrl(string $imagePath = null): ?string
    {
        if (!$imagePath) {
            return null;
        }

        return Storage::disk('public')->url($imagePath);
    }

    /**
     * Handle image upload for model attributes
     *
     * @param array $data
     * @param string $imageField
     * @param string $directory
     * @param string|null $oldImagePath
     * @return array
     */
    public function handleImageUpload(array $data, string $imageField = 'image', string $directory = 'images', string $oldImagePath = null): array
    {
        if (isset($data[$imageField]) && $data[$imageField] instanceof UploadedFile) {
            $data[$imageField] = $this->uploadImage($data[$imageField], $directory, $oldImagePath);
        } else {
            // If no new image uploaded, keep the old one or remove the field
            if ($oldImagePath) {
                $data[$imageField] = $oldImagePath;
            } else {
                unset($data[$imageField]);
            }
        }

        return $data;
    }

    /**
     * Validate image file
     *
     * @param UploadedFile $image
     * @param int $maxSize Maximum size in KB (default 2MB)
     * @param array $allowedTypes
     * @return array|null Returns error array if validation fails, null if passes
     */
    public function validateImage(UploadedFile $image, int $maxSize = 2048, array $allowedTypes = ['jpeg', 'jpg', 'png', 'gif', 'webp']): ?array
    {
        $errors = [];

        // Check file size (convert KB to bytes)
        if ($image->getSize() > ($maxSize * 1024)) {
            $errors[] = "Image size must be less than {$maxSize}KB";
        }

        // Check file type
        $extension = strtolower($image->getClientOriginalExtension());
        if (!in_array($extension, $allowedTypes)) {
            $errors[] = "Image must be one of the following types: " . implode(', ', $allowedTypes);
        }

        // Check if it's actually an image
        if (!getimagesize($image->getPathname())) {
            $errors[] = "Uploaded file is not a valid image";
        }

        return empty($errors) ? null : $errors;
    }

    /**
     * Resize image if needed (requires intervention/image package)
     * This is optional and requires: composer require intervention/image
     *
     * @param string $imagePath
     * @param int $maxWidth
     * @param int $maxHeight
     * @param bool $maintainRatio
     * @return string
     */
    public function resizeImage(string $imagePath, int $maxWidth = 800, int $maxHeight = 600, bool $maintainRatio = true): string
    {
        // This method requires intervention/image package
        // Uncomment and modify if you install the package:
        
        /*
        $fullPath = Storage::disk('public')->path($imagePath);
        
        if (class_exists('\Intervention\Image\ImageManagerStatic')) {
            $image = \Intervention\Image\ImageManagerStatic::make($fullPath);
            
            if ($maintainRatio) {
                $image->resize($maxWidth, $maxHeight, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            } else {
                $image->resize($maxWidth, $maxHeight);
            }
            
            $image->save($fullPath);
        }
        */
        
        return $imagePath;
    }

    /**
     * Upload multiple images and return array of paths
     *
     * @param array $images Array of UploadedFile objects
     * @param string $directory
     * @return array
     */
    public function uploadMultipleImages(array $images, string $directory = 'images'): array
    {
        $uploadedPaths = [];

        foreach ($images as $image) {
            if ($image instanceof UploadedFile) {
                $uploadedPaths[] = $this->uploadImage($image, $directory);
            }
        }

        return $uploadedPaths;
    }

    /**
     * Delete multiple images from storage
     *
     * @param array|null $imagePaths
     * @return bool
     */
    public function deleteMultipleImages(array $imagePaths = null): bool
    {
        if (!$imagePaths || empty($imagePaths)) {
            return false;
        }

        $allDeleted = true;
        foreach ($imagePaths as $imagePath) {
            if (!$this->deleteImage($imagePath)) {
                $allDeleted = false;
            }
        }

        return $allDeleted;
    }
}