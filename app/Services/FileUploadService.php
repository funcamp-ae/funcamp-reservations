<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JildertMiedema\LaravelPlupload\Facades\Plupload;

class FileUploadService
{
    /**
     * Handle chunked file upload using Plupload
     *
     * @param string $fieldName The name of the file input field
     * @param string $directory The directory to store the uploaded file (relative to storage/app/public)
     * @param array $allowedTypes Array of allowed MIME types
     * @param int $maxSize Maximum file size in MB
     * @return array Returns ['success' => bool, 'path' => string|null, 'url' => string|null, 'message' => string]
     */
    public function handleChunkedUpload($fieldName = 'file', $directory = 'chat/uploads', $allowedTypes = [], $maxSize = 1024)
    {
        try {
            // Default allowed types for images and videos
            if (empty($allowedTypes)) {
                $allowedTypes = [
                    'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
                    'video/mp4', 'video/mpeg', 'video/quicktime', 'video/x-msvideo', 'video/webm',"video/mov"
                ];
            }

            $result = Plupload::receive($fieldName, function ($file) use ($directory, $allowedTypes, $maxSize) {
                // Validate file type
                $mimeType = $file->getMimeType();
                if (!in_array($mimeType, $allowedTypes)) {
                    throw new \Exception('File type not allowed. Allowed types: ' . implode(', ', $allowedTypes));
                }

                // Validate file size (in bytes)
                $maxSizeBytes = $maxSize * 1024 * 1024;
                if ($file->getSize() > $maxSizeBytes) {
                    throw new \Exception('File size exceeds maximum allowed size of ' . $maxSize . 'MB');
                }

                // Generate unique filename
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $filename = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '_' . time() . '.' . $extension;

                // Store file using Laravel Storage facade
                $relativePath = $directory . '/' . $filename;
                Storage::disk('public')->put($relativePath, file_get_contents($file->getRealPath()));

                return $filename;
            });

            // Check if upload was successful
            if ($result === 'ready' || is_string($result)) {
                $filename = is_string($result) ? $result : null;
                
                if ($filename) {
                    $relativePath = $directory . '/' . $filename;
                    $url = Storage::url($relativePath);

                    return [
                        'success' => true,
                        'path' => $relativePath,
                        'url' => asset($url),
                        'message' => 'File uploaded successfully'
                    ];
                }
            }

            return [
                'success' => false,
                'path' => null,
                'url' => null,
                'message' => 'Upload failed'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'path' => null,
                'url' => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete a file from storage
     *
     * @param string $path The relative path to the file (e.g., 'chat/uploads/filename.jpg')
     * @return bool
     */
    public function deleteFile($path)
    {
        try {
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->delete($path);
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get file type from path or mime type
     *
     * @param string $path
     * @return string 'image', 'video', or 'file'
     */
    public function getFileType($path)
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $videoExtensions = ['mp4', 'mpeg', 'mov', 'avi', 'webm', 'mkv'];

        if (in_array($extension, $imageExtensions)) {
            return 'image';
        } elseif (in_array($extension, $videoExtensions)) {
            return 'video';
        }

        return 'file';
    }

    /**
     * Get human-readable file size
     *
     * @param int $bytes
     * @return string
     */
    public function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}
