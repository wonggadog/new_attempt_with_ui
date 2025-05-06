<?php
namespace App\Http\Controllers;

use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GoogleDriveController extends Controller
{
    protected $driveService;

    public function __construct(GoogleDriveService $driveService)
    {
        $this->driveService = $driveService;
    }

    public function connect()
    {
        $authUrl = $this->driveService->getAuthUrl();
        return redirect($authUrl);
    }

    public function callback(Request $request)
    {
        try {
            $code = $request->get('code');
            $token = $this->driveService->fetchAccessToken($code);
            $user = Auth::user();

            $user->connectGoogleDrive(
                json_encode($token),
                $token['refresh_token'] ?? null
            );

            // Create folder if not exists
            if (!$user->google_drive_folder_id) {
                $folderName = "DMS Documents - " . $user->name;
                $folderId = $this->driveService->createFolder($folderName);
                $user->setGoogleDriveFolderId($folderId);
            }

            return redirect()->route('home')->with('success', 'Google Drive connected successfully!');
        } catch (\Exception $e) {
            Log::error('Google Drive connection failed: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Failed to connect Google Drive. Please try again.');
        }
    }

    public function disconnect()
    {
        try {
            $user = Auth::user();
            $user->disconnectGoogleDrive();
            return redirect()->route('home')->with('success', 'Google Drive disconnected successfully!');
        } catch (\Exception $e) {
            Log::error('Google Drive disconnection failed: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Failed to disconnect Google Drive. Please try again.');
        }
    }

    public function uploadToRecipient(Request $request, $recipientId)
    {
        try {
            $recipient = \App\Models\User::findOrFail($recipientId);

            if (!$recipient->google_drive_connected) {
                return response()->json([
                    'success' => false,
                    'message' => 'Recipient has not connected their Google Drive account.'
                ], 400);
            }

            // Decode stored token
            $token = json_decode($recipient->google_drive_token, true);

            // Refresh token if expired
            if ($this->isTokenExpired($token)) {
                $refreshToken = $recipient->google_drive_refresh_token;
                if (!$refreshToken) {
                    throw new \Exception("No refresh token available for recipient ID: $recipientId");
                }
                $token = $this->driveService->refreshToken($refreshToken);
                $recipient->update([
                    'google_drive_token' => json_encode($token)
                ]);
            }

            // Set refreshed token
            $this->driveService->setAccessToken($token);

            // Upload file
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $filePath = $file->getPathname();

            $fileId = $this->driveService->uploadFile($filePath, $fileName, $recipient->google_drive_folder_id);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded to recipient\'s Google Drive successfully.',
                'file_id' => $fileId
            ]);
        } catch (\Exception $e) {
            Log::error('Google Drive upload failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file to Google Drive: ' . $e->getMessage()
            ], 500);
        }
    }

    private function isTokenExpired(array $token): bool
    {
        return !isset($token['expires_in']) || $token['expires_in'] < time();
    }
}