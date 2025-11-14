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
                    'video/mp4', 'video/mpeg', 'video/quicktime', 'video/x-msvideo', 'video/webm'
                ];
            }

            $result = Plupload::receive($fieldName, function ($file) use ($directory, $allowedTypes, $maxSize) {
                // Validate file type
                $mimeType = $file->getMimeType();
                
                \Log::info('Plupload file received', [
                    'mime' => $mimeType,
                    'size' => $file->getSize(),
                    'name' => $file->getClientOriginalName()
                ]);
                
                if (!empty($allowedTypes) && !in_array($mimeType, $allowedTypes)) {
                    throw new \Exception('File type not allowed: ' . $mimeType);
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

                // Store file
                $targetPath = storage_path('app/public/' . $directory);
                if (!file_exists($targetPath)) {
                    mkdir($targetPath, 0777, true);
                }
                
                $file->move($targetPath, $filename);

                \Log::info('File stored successfully', ['filename' => $filename]);

                // Return the filename (Plupload will handle returning 'ready' for chunks)
                return $filename;
            });

            \Log::info('Plupload result', ['result' => $result, 'type' => gettype($result)]);

            // Plupload returns 'ready' when chunks are being processed
            // and the filename when upload is complete
            if ($result === 'ready') {
                // Chunk received, waiting for more
                return [
                    'success' => true,
                    'path' => null,
                    'url' => null,
                    'message' => 'Chunk received',
                    'status' => 'chunking'
                ];
            }
            
            if (is_string($result) && $result !== 'ready') {
                // Upload complete - result is the filename
                $filename = $result;
                $relativePath = $directory . '/' . $filename;
                $url = Storage::url($relativePath);

                return [
                    'success' => true,
                    'path' => $relativePath,
                    'url' => asset($url),
                    'message' => 'File uploaded successfully',
                    'status' => 'complete'
                ];
            }

            // If we get here, something unexpected happened
            return [
                'success' => false,
                'path' => null,
                'url' => null,
                'message' => 'Unexpected response from Plupload: ' . json_encode($result)
            ];

        } catch (\Exception $e) {
            \Log::error('Upload error in handleChunkedUpload', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'path' => null,
                'url' => null,
                'message' => $e->getMessage()
            ];
        }
    }
    /**
     * Handle standard Laravel file upload (fallback)
     */
    private function handleStandardUpload($fieldName, $directory, $allowedTypes, $maxSize)
    {
        $request = request();
        
        if (!$request->hasFile($fieldName)) {
            throw new \Exception('No file uploaded');
        }

        $file = $request->file($fieldName);

        // Validate file
        if (!$file->isValid()) {
            throw new \Exception('Invalid file upload');
        }

        // Validate file type
        $mimeType = $file->getMimeType();
        if (!empty($allowedTypes) && !in_array($mimeType, $allowedTypes)) {
            throw new \Exception('File type not allowed: ' . $mimeType);
        }

        // Validate file size
        $maxSizeBytes = $maxSize * 1024 * 1024;
        if ($file->getSize() > $maxSizeBytes) {
            throw new \Exception('File size exceeds maximum allowed size of ' . $maxSize . 'MB');
        }

        // Generate unique filename
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $filename = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '_' . time() . '.' . $extension;

        // Store file
        $relativePath = $directory . '/' . $filename;
        $file->storeAs('public/' . $directory, $filename);

        $url = Storage::url($relativePath);

        return [
            'success' => true,
            'path' => $relativePath,
            'url' => asset($url),
            'message' => 'File uploaded successfully'
        ];
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
