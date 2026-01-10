<?php

if (!function_exists('getFileUrl')) {
    /**
     * Get file URL from Google Drive file ID or local path
     *
     * @param string|null $fileIdOrPath Google Drive file ID or local file path
     * @param bool $thumbnail Whether to return thumbnail URL (for images)
     * @return string File URL
     */
    function getFileUrl(?string $fileIdOrPath, bool $thumbnail = false): string
    {
        if (empty($fileIdOrPath)) {
            return '';
        }

        // Check if it's a Google Drive file ID (typically contains no slashes or is a long alphanumeric string)
        // Or if it starts with a folder path, it's a local file
        if (strpos($fileIdOrPath, '/') !== false || strpos($fileIdOrPath, '\\') !== false) {
            // Local file path
            return asset('storage/' . $fileIdOrPath);
        }

        // Assume it's a Google Drive file ID
        try {
            $driveService = app(\App\Services\GoogleDriveService::class);
            return $driveService->getFileUrl($fileIdOrPath, $thumbnail);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to get Google Drive URL: ' . $e->getMessage());
            // Fallback to local path
            return asset('storage/' . $fileIdOrPath);
        }
    }
}

