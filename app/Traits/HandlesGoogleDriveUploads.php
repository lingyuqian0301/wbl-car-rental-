<?php

namespace App\Traits;

use App\Services\GoogleDriveService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

trait HandlesGoogleDriveUploads
{
    /**
     * Upload file to Google Drive
     *
     * @param UploadedFile $file
     * @param string $folderPath Folder path in Google Drive (e.g., 'vehicle_photos', 'customer_documents') OR folder ID
     * @param string|null $fileName Custom file name
     * @param bool $folderPathIsId If true, treat $folderPath as a folder ID instead of path
     * @return string|array Google Drive file ID (legacy) or array with 'fileId' and 'fileUrl' (new)
     */
    protected function uploadToGoogleDrive(UploadedFile $file, string $folderPath = '', ?string $fileName = null, bool $folderPathIsId = false)
    {
        try {
            $driveService = new GoogleDriveService();
            $finalFileName = $fileName ?? (time() . '_' . $file->getClientOriginalName());
            $result = $driveService->uploadFile($file, $folderPath, $finalFileName, $folderPathIsId);
            
            // Return file ID for backward compatibility, or full result array
            return is_array($result) ? $result['fileId'] : $result;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google Drive upload failed: ' . $e->getMessage());
            // Fallback to local storage if Google Drive fails
            $fileName = $fileName ?? (time() . '_' . $file->getClientOriginalName());
            $filePath = $file->storeAs($folderPath, $fileName, 'public');
            return $filePath; // Return local path as fallback
        }
    }

    /**
     * Upload file to Google Drive and get full result (file ID and URL)
     *
     * @param UploadedFile $file
     * @param string $folderPathOrId Folder path or folder ID
     * @param string|null $fileName Custom file name
     * @param bool $isFolderId If true, treat $folderPathOrId as folder ID
     * @return array Array with 'fileId' and 'fileUrl'
     */
    protected function uploadToGoogleDriveWithUrl(UploadedFile $file, string $folderPathOrId = '', ?string $fileName = null, bool $isFolderId = false): array
    {
        try {
            $driveService = new GoogleDriveService();
            $finalFileName = $fileName ?? (time() . '_' . $file->getClientOriginalName());
            return $driveService->uploadFile($file, $folderPathOrId, $finalFileName, $isFolderId);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google Drive upload failed: ' . $e->getMessage());
            // Fallback to local storage if Google Drive fails
            $fileName = $fileName ?? (time() . '_' . $file->getClientOriginalName());
            $filePath = $file->storeAs($folderPathOrId, $fileName, 'public');
            return [
                'fileId' => $filePath,
                'fileUrl' => asset('storage/' . $filePath),
            ];
        }
    }

    /**
     * Get file URL from Google Drive file ID or local path
     *
     * @param string $fileIdOrPath Google Drive file ID or local file path
     * @param bool $thumbnail Whether to return thumbnail URL (for images)
     * @return string File URL
     */
    protected function getFileUrl(string $fileIdOrPath, bool $thumbnail = false): string
    {
        // Check if it's a Google Drive file ID (typically contains no slashes or is a long alphanumeric string)
        // Or if it starts with a folder path, it's a local file
        if (strpos($fileIdOrPath, '/') !== false || strpos($fileIdOrPath, '\\') !== false) {
            // Local file path
            return asset('storage/' . $fileIdOrPath);
        }

        // Assume it's a Google Drive file ID
        try {
            $driveService = new GoogleDriveService();
            return $driveService->getFileUrl($fileIdOrPath, $thumbnail);
        } catch (\Exception $e) {
            Log::error('Failed to get Google Drive URL: ' . $e->getMessage());
            // Fallback to local path
            return asset('storage/' . $fileIdOrPath);
        }
    }

    /**
     * Delete file from Google Drive or local storage
     *
     * @param string $fileIdOrPath Google Drive file ID or local file path
     * @return bool
     */
    protected function deleteFile(string $fileIdOrPath): bool
    {
        // Check if it's a local file path
        if (strpos($fileIdOrPath, '/') !== false || strpos($fileIdOrPath, '\\') !== false) {
            // Local file
            try {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($fileIdOrPath);
                return true;
            } catch (\Exception $e) {
                Log::error('Failed to delete local file: ' . $e->getMessage());
                return false;
            }
        }

        // Google Drive file ID
        try {
            $driveService = new GoogleDriveService();
            return $driveService->deleteFile($fileIdOrPath);
        } catch (\Exception $e) {
            Log::error('Failed to delete Google Drive file: ' . $e->getMessage());
            return false;
        }
    }
}

