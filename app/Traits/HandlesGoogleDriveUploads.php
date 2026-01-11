<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait HandlesGoogleDriveUploads
{
    /**
     * The disk to use for uploads - configured in config/filesystems.php
     * Points to: C:\xampp\htdocs\myportfolio\public
     */
    protected string $uploadDisk = 'wbl_public';

    /**
     * Upload file to local public folder
     * 
     * This replaces Google Drive uploads and stores files locally in:
     * C:\xampp\htdocs\myportfolio\public\{folderPath}
     *
     * @param UploadedFile $file
     * @param string $folderPath Folder path (e.g., 'uploads/owner_ic', 'uploads/car_images')
     * @param string|null $fileName Custom file name
     * @param bool $folderPathIsId Ignored - kept for backward compatibility
     * @return string File path relative to disk root
     */
    protected function uploadToGoogleDrive(UploadedFile $file, string $folderPath = '', ?string $fileName = null, bool $folderPathIsId = false)
    {
        try {
            // Normalize folder path - ensure it starts with 'uploads/'
            if (!str_starts_with($folderPath, 'uploads/')) {
                $folderPath = 'uploads/' . $folderPath;
            }
            
            $finalFileName = $fileName ?? (time() . '_' . $file->getClientOriginalName());
            
            // Store file to wbl_public disk (C:\xampp\htdocs\myportfolio\public)
            $filePath = $file->storeAs($folderPath, $finalFileName, $this->uploadDisk);
            
            Log::info('File uploaded to myportfolio public: ' . $filePath);
            
            return $filePath;
        } catch (\Exception $e) {
            Log::error('File upload failed: ' . $e->getMessage());
            
            // Fallback to default public storage
            $fileName = $fileName ?? (time() . '_' . $file->getClientOriginalName());
            $filePath = $file->storeAs($folderPath, $fileName, 'public');
            return $filePath;
        }
    }

    /**
     * Upload file to local public folder and get full result
     *
     * @param UploadedFile $file
     * @param string $folderPathOrId Folder path
     * @param string|null $fileName Custom file name
     * @param bool $isFolderId Ignored - kept for backward compatibility
     * @return array Array with 'fileId' and 'fileUrl'
     */
    protected function uploadToGoogleDriveWithUrl(UploadedFile $file, string $folderPathOrId = '', ?string $fileName = null, bool $isFolderId = false): array
    {
        try {
            // Normalize folder path - ensure it starts with 'uploads/'
            if (!str_starts_with($folderPathOrId, 'uploads/')) {
                $folderPathOrId = 'uploads/' . $folderPathOrId;
            }
            
            $finalFileName = $fileName ?? (time() . '_' . $file->getClientOriginalName());
            
            // Store file to wbl_public disk (myportfolio/public)
            $filePath = $file->storeAs($folderPathOrId, $finalFileName, $this->uploadDisk);
            
            // Get the URL - use asset() for local public folder
            $url = asset($filePath);
            
            Log::info('File uploaded to myportfolio public with URL: ' . $url);
            
            return [
                'fileId' => $filePath,
                'fileUrl' => $url,
            ];
        } catch (\Exception $e) {
            Log::error('File upload failed: ' . $e->getMessage());
            
            // Fallback to default public storage
            $fileName = $fileName ?? (time() . '_' . $file->getClientOriginalName());
            $filePath = $file->storeAs($folderPathOrId, $fileName, 'public');
            return [
                'fileId' => $filePath,
                'fileUrl' => asset('storage/' . $filePath),
            ];
        }
    }

    /**
     * Get file URL from file path
     *
     * @param string $fileIdOrPath File path
     * @param bool $thumbnail Ignored - kept for backward compatibility
     * @return string File URL
     */
    protected function getFileUrl(string $fileIdOrPath, bool $thumbnail = false): string
    {
        // Check if it's an uploads path (stored in public folder)
        if (str_starts_with($fileIdOrPath, 'uploads/')) {
            return asset($fileIdOrPath);
        }
        
        // Check if it's already a full URL
        if (str_starts_with($fileIdOrPath, 'http://') || str_starts_with($fileIdOrPath, 'https://')) {
            return $fileIdOrPath;
        }
        
        // Check if it's a local file path with slashes
        if (strpos($fileIdOrPath, '/') !== false || strpos($fileIdOrPath, '\\') !== false) {
            // Try to find in public folder first
            if (file_exists(public_path($fileIdOrPath))) {
                return asset($fileIdOrPath);
            }
            // Fallback to local public storage
            return asset('storage/' . $fileIdOrPath);
        }

        // Fallback - return as storage path
            return asset('storage/' . $fileIdOrPath);
    }

    /**
     * Delete file from storage
     *
     * @param string $fileIdOrPath File path
     * @return bool
     */
    protected function deleteFile(string $fileIdOrPath): bool
    {
            try {
            // Try wbl_public disk first
            if (Storage::disk($this->uploadDisk)->exists($fileIdOrPath)) {
                Storage::disk($this->uploadDisk)->delete($fileIdOrPath);
                Log::info('File deleted from wbl_public: ' . $fileIdOrPath);
                return true;
            }
            
            // Fallback to default public storage
            if (Storage::disk('public')->exists($fileIdOrPath)) {
                Storage::disk('public')->delete($fileIdOrPath);
                return true;
            }
            
            Log::warning('File not found for deletion: ' . $fileIdOrPath);
            return false;
        } catch (\Exception $e) {
            Log::error('Failed to delete file: ' . $e->getMessage());
            return false;
        }
    }
}

