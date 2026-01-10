<?php

namespace App\Services;

use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    private $client;
    private $service;
    private $folderId;

    public function __construct()
    {
        $this->initializeClient();
        $this->folderId = config('filesystems.disks.gdrive.folder_id', 'root');
    }

    /**
     * Initialize Google Drive client
     */
    private function initializeClient()
    {
        try {
            $credentialsPath = config_path('google-credentials.json');
            
            if (!file_exists($credentialsPath)) {
                Log::warning('Google Drive credentials file not found at: ' . $credentialsPath);
                throw new \Exception('Google Drive credentials file not found. Please follow the setup guide in GOOGLE_DRIVE_SETUP.md');
            }

            $this->client = new Google_Client();
            $this->client->setApplicationName(config('app.name', 'My Portfolio'));
            $this->client->setScopes(Google_Service_Drive::DRIVE);
            $this->client->setAuthConfig($credentialsPath);
            $this->client->setAccessType('offline');
            $this->client->setPrompt('select_account consent');

            // Get or refresh access token
            $tokenPath = storage_path('app/google-drive-token.json');
            
            if (file_exists($tokenPath)) {
                $accessToken = json_decode(file_get_contents($tokenPath), true);
                if ($accessToken) {
                    $this->client->setAccessToken($accessToken);
                }
            }

            // Refresh token if expired
            if ($this->client->isAccessTokenExpired()) {
                if ($this->client->getRefreshToken()) {
                    $newToken = $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                    if (isset($newToken['error'])) {
                        throw new \Exception('Failed to refresh Google Drive token: ' . $newToken['error_description'] ?? $newToken['error']);
                    }
                } else {
                    // Get new token
                    $authUrl = $this->client->createAuthUrl();
                    Log::warning('Google Drive token expired. Please visit: ' . $authUrl);
                    throw new \Exception('Google Drive access token expired. Please re-authorize. Visit: ' . $authUrl);
                }

                // Save new token
                if (!file_exists(dirname($tokenPath))) {
                    mkdir(dirname($tokenPath), 0700, true);
                }
                file_put_contents($tokenPath, json_encode($this->client->getAccessToken()));
            }

            $this->service = new Google_Service_Drive($this->client);
        } catch (\Exception $e) {
            Log::error('Google Drive initialization failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Upload file to Google Drive
     *
     * @param UploadedFile|string $file File to upload or file path
     * @param string $folderPath Folder path in Google Drive (e.g., 'vehicle_photos', 'customer_documents') OR folder ID
     * @param string|null $fileName Custom file name
     * @param bool $folderPathIsId If true, treat $folderPath as a folder ID instead of path
     * @return array Array with 'fileId' and 'fileUrl'
     */
    public function uploadFile($file, string $folderPath = '', ?string $fileName = null, bool $folderPathIsId = false): array
    {
        try {
            // Get file content
            if ($file instanceof UploadedFile) {
                $fileContent = file_get_contents($file->getRealPath());
                $mimeType = $file->getMimeType();
                $originalName = $fileName ?? $file->getClientOriginalName();
            } else {
                // Assume it's a file path
                $fileContent = file_get_contents($file);
                $mimeType = mime_content_type($file);
                $originalName = $fileName ?? basename($file);
            }

            // Get folder ID - if folderPathIsId is true, use it directly, otherwise get/create folder
            $folderId = null;
            if ($folderPathIsId && !empty($folderPath)) {
                $folderId = $folderPath; // Use folder ID directly
            } else {
                $folderId = $this->getOrCreateFolder($folderPath);
            }

            // Create file metadata
            $driveFile = new Google_Service_Drive_DriveFile([
                'name' => $originalName,
                'parents' => $folderId ? [$folderId] : [],
            ]);

            // Upload file
            $uploadedFile = $this->service->files->create(
                $driveFile,
                [
                    'data' => $fileContent,
                    'mimeType' => $mimeType,
                    'uploadType' => 'multipart',
                    'fields' => 'id, webViewLink, webContentLink',
                ]
            );

            // Make file publicly accessible (optional, adjust permissions as needed)
            // $this->setFilePermission($uploadedFile->id, 'reader', 'anyone');

            // Get file URL
            $fileUrl = $uploadedFile->webContentLink ?? $uploadedFile->webViewLink ?? "https://drive.google.com/file/d/{$uploadedFile->id}/view";

            // Return array with file ID and URL
            return [
                'fileId' => $uploadedFile->id,
                'fileUrl' => $fileUrl,
            ];
        } catch (\Exception $e) {
            Log::error('Google Drive upload failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get file URL by file ID
     *
     * @param string $fileId Google Drive file ID
     * @param bool $thumbnail Whether to return thumbnail URL
     * @return string File URL
     */
    public function getFileUrl(string $fileId, bool $thumbnail = false): string
    {
        try {
            if ($thumbnail) {
                return "https://drive.google.com/thumbnail?id={$fileId}&sz=w1000";
            }

            // Try to get web view link
            $file = $this->service->files->get($fileId, ['fields' => 'webViewLink, webContentLink']);
            
            // Return webContentLink if available (direct download), otherwise webViewLink
            return $file->webContentLink ?? $file->webViewLink ?? "https://drive.google.com/file/d/{$fileId}/view";
        } catch (\Exception $e) {
            Log::error('Failed to get Google Drive file URL: ' . $e->getMessage());
            // Fallback to basic URL
            return "https://drive.google.com/file/d/{$fileId}/view";
        }
    }

    /**
     * Delete file from Google Drive
     *
     * @param string $fileIdOrUrl Google Drive file ID or URL
     * @return bool
     */
    public function deleteFile(string $fileIdOrUrl): bool
    {
        try {
            // Extract file ID from URL if it's a Google Drive URL
            $fileId = $this->extractFileIdFromUrl($fileIdOrUrl);
            
            if (!$fileId) {
                Log::warning('Could not extract file ID from: ' . $fileIdOrUrl);
                return false;
            }
            
            $this->service->files->delete($fileId);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete Google Drive file: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Extract Google Drive file ID from URL
     *
     * @param string $url Google Drive URL or file ID
     * @return string|null File ID
     */
    private function extractFileIdFromUrl(string $url): ?string
    {
        // If it's already a file ID (no slashes, just alphanumeric), return as is
        if (!strpos($url, '/') && !strpos($url, '\\') && !strpos($url, ':')) {
            return $url;
        }

        // Try to extract from Google Drive URL patterns
        // Pattern 1: https://drive.google.com/file/d/{FILE_ID}/view
        preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }

        // Pattern 2: https://drive.google.com/open?id={FILE_ID}
        preg_match('/[?&]id=([a-zA-Z0-9_-]+)/', $url, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }

        // Pattern 3: https://drive.google.com/uc?id={FILE_ID}
        preg_match('/uc\?id=([a-zA-Z0-9_-]+)/', $url, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Get or create folder in Google Drive
     *
     * @param string $folderPath Folder path (e.g., 'vehicle_photos', 'customer_documents/2024')
     * @return string|null Folder ID
     */
    private function getOrCreateFolder(string $folderPath): ?string
    {
        if (empty($folderPath) || $folderPath === 'root') {
            return $this->folderId !== 'root' ? $this->folderId : null;
        }

        try {
            $parts = explode('/', $folderPath);
            $currentFolderId = $this->folderId !== 'root' ? $this->folderId : null;

            foreach ($parts as $folderName) {
                if (empty($folderName)) {
                    continue;
                }

                // Search for existing folder
                $query = "name='{$folderName}' and mimeType='application/vnd.google-apps.folder' and trashed=false";
                if ($currentFolderId) {
                    $query .= " and '{$currentFolderId}' in parents";
                } else {
                    $query .= " and 'root' in parents";
                }

                $response = $this->service->files->listFiles([
                    'q' => $query,
                    'fields' => 'files(id, name)',
                ]);

                if (count($response->files) > 0) {
                    // Folder exists
                    $currentFolderId = $response->files[0]->id;
                } else {
                    // Create folder
                    $folder = new Google_Service_Drive_DriveFile([
                        'name' => $folderName,
                        'mimeType' => 'application/vnd.google-apps.folder',
                    ]);

                    if ($currentFolderId) {
                        $folder->setParents([$currentFolderId]);
                    }

                    $createdFolder = $this->service->files->create($folder, ['fields' => 'id']);
                    $currentFolderId = $createdFolder->id;
                }
            }

            return $currentFolderId;
        } catch (\Exception $e) {
            Log::error('Failed to get/create Google Drive folder: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Set file permission (make public or private)
     *
     * @param string $fileId Google Drive file ID
     * @param string $role 'reader' or 'writer'
     * @param string $type 'user', 'group', 'domain', or 'anyone'
     * @param string|null $emailAddress Email if type is 'user' or 'group'
     * @return bool
     */
    private function setFilePermission(string $fileId, string $role = 'reader', string $type = 'anyone', ?string $emailAddress = null): bool
    {
        try {
            $permission = new \Google_Service_Drive_Permission();
            $permission->setRole($role);
            $permission->setType($type);

            if ($emailAddress) {
                $permission->setEmailAddress($emailAddress);
            }

            $this->service->permissions->create(
                $fileId,
                $permission,
                ['fields' => 'id']
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to set Google Drive file permission: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get authorization URL for first-time setup
     *
     * @return string Authorization URL
     */
    public function getAuthUrl(): string
    {
        try {
            if (!$this->client) {
                $this->initializeClient();
            }
            return $this->client->createAuthUrl();
        } catch (\Exception $e) {
            Log::error('Failed to get Google Drive auth URL: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle OAuth callback and save token
     *
     * @param string $code Authorization code
     * @return bool
     */
    public function handleCallback(string $code): bool
    {
        try {
            if (!$this->client) {
                $this->initializeClient();
            }

            $accessToken = $this->client->fetchAccessTokenWithAuthCode($code);
            $this->client->setAccessToken($accessToken);

            // Check for errors
            if (array_key_exists('error', $accessToken)) {
                throw new \Exception(json_encode($accessToken, JSON_PRETTY_PRINT));
            }

            // Save token
            $tokenPath = storage_path('app/google-drive-token.json');
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($this->client->getAccessToken()));

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to handle Google Drive callback: ' . $e->getMessage());
            return false;
        }
    }
}

