<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->scopes([
                'openid', 'profile', 'email',
                'https://www.googleapis.com/auth/drive'
            ])
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Check if the email domain is bicol-u.edu.ph
            if (!str_ends_with($googleUser->getEmail(), '@bicol-u.edu.ph')) {
                return redirect()->route('login')
                    ->with('error', 'Only Bicol University email addresses (@bicol-u.edu.ph) are allowed to login.');
            }

            // Find or create user
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Generate a random id_number for new users
                $id_number = 'G-' . strtoupper(Str::random(8));
                
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'id_number' => $id_number,
                    'password' => bcrypt(uniqid()), // Random password since we're using Google auth
                ]);
            } else {
                // Update user's Google ID if it's not set
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
            }

            // Store Google Drive tokens on login
            $accessToken = $googleUser->token;
            $refreshToken = $googleUser->refreshToken;
            $tokenArr = [
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_in' => $googleUser->expiresIn,
            ];
            $user->google_drive_token = json_encode($tokenArr);
            $user->google_drive_refresh_token = $refreshToken;
            $user->google_drive_connected = true;
            $user->save();

            // Create DMS Documents folder if not exists
            if (!$user->google_drive_folder_id) {
                $driveService = app(\App\Services\GoogleDriveService::class);
                $driveService->setAccessToken($tokenArr);
                $folderName = "BUCS DocuManage - " . $user->name;
                $folderId = $driveService->createFolder($folderName);
                $user->setGoogleDriveFolderId($folderId);
            }

            // Log the user in
            Auth::login($user);
            
            // Regenerate session
            session()->regenerate();

            // Redirect to home route
            return redirect('/');

        } catch (\Exception $e) {
            \Log::error('Google authentication error: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'Something went wrong with Google authentication. Please try again.');
        }
    }
} 