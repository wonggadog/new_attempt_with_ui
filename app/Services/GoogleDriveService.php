<?php

namespace App\Services;

use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class GoogleDriveService
{
    protected $client;
    protected $drive;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect'));
        $this->client->addScope([
            'https://www.googleapis.com/auth/drive.file',
            'https://www.googleapis.com/auth/drive.appdata'
        ]);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
    }

    public function setAccessToken($token)
    {
        $this->client->setAccessToken($token);
        $this->drive = new Google_Service_Drive($this->client);
    }

    public function createFolder($folderName, $parentId = null)
    {
        try {
            $fileMetadata = new Google_Service_Drive_DriveFile([
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder'
            ]);

            if ($parentId) {
                $fileMetadata->setParents([$parentId]);
            }

            $folder = $this->drive->files->create($fileMetadata, [
                'fields' => 'id'
            ]);

            return $folder->id;
        } catch (\Exception $e) {
            Log::error('Google Drive folder creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function uploadFile($filePath, $fileName, $folderId = null)
    {
        try {
            $fileMetadata = new Google_Service_Drive_DriveFile([
                'name' => $fileName
            ]);

            if ($folderId) {
                $fileMetadata->setParents([$folderId]);
            }

            // Add logging to check if the file content is being read and transferred
            Log::info('Attempting to read file content from path: ' . $filePath);
            $content = file_get_contents($filePath);
            if ($content === false) {
                Log::error('Failed to read file content from path: ' . $filePath);
            } else {
                Log::info('File content successfully read from path: ' . $filePath);
            }

            Log::info('Attempting to upload file to Google Drive with name: ' . $fileName);
            $file = $this->drive->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => mime_content_type($filePath),
                'uploadType' => 'multipart',
                'fields' => 'id'
            ]);
            Log::info('File successfully uploaded to Google Drive with ID: ' . $file->id);

            return $file->id;
        } catch (\Exception $e) {
            Log::error('Google Drive file upload failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function refreshToken($refreshToken)
    {
        try {
            $this->client->refreshToken($refreshToken);
            return $this->client->getAccessToken();
        } catch (\Exception $e) {
            Log::error('Google Drive token refresh failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    public function fetchAccessToken($code)
    {
        try {
            $token = $this->client->fetchAccessTokenWithAuthCode($code);
            return $token;
        } catch (\Exception $e) {
            Log::error('Google Drive token fetch failed: ' . $e->getMessage());
            throw $e;
        }
    }
}