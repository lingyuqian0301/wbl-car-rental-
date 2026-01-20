<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

if (!function_exists('getFileUrl')) {
    /**
     * Get file URL from file path
     * 
     * Supports:
     * - Local public folder paths (uploads/*) stored in C:\xampp\htdocs\myportfolio\public
     * - Local storage paths
     * - Google Drive file IDs (legacy support)
     *
     * @param string|null $fileIdOrPath File path or legacy Google Drive ID
     * @param bool $thumbnail Ignored - kept for backward compatibility
     * @return string File URL
     */
    function getFileUrl(?string $fileIdOrPath, bool $thumbnail = false): string
    {
        if (empty($fileIdOrPath)) {
            return '';
        }

        // Check if it's already a full URL
        if (str_starts_with($fileIdOrPath, 'http://') || str_starts_with($fileIdOrPath, 'https://')) {
            return $fileIdOrPath;
        }

        // Check if it's an uploads path (stored in public folder)
        if (str_starts_with($fileIdOrPath, 'uploads/')) {
            return asset($fileIdOrPath);
        }

        // Check if it's a local file path with slashes
        if (strpos($fileIdOrPath, '/') !== false || strpos($fileIdOrPath, '\\') !== false) {
            // If path already starts with 'storage/', don't add it again
            if (str_starts_with($fileIdOrPath, 'storage/')) {
                return asset($fileIdOrPath);
            }
            
            // Try to find in public folder first
            if (file_exists(public_path($fileIdOrPath))) {
                return asset($fileIdOrPath);
            }
            
            // Fallback to local public storage
            return asset('storage/' . $fileIdOrPath);
        }

        // Legacy Google Drive support - try to get from Google Drive service
        try {
            $driveService = app(\App\Services\GoogleDriveService::class);
            return $driveService->getFileUrl($fileIdOrPath, $thumbnail);
        } catch (\Exception $e) {
            Log::error('Failed to get Google Drive URL: ' . $e->getMessage());
            // Fallback to local path
            return asset('storage/' . $fileIdOrPath);
        }
    }
}

