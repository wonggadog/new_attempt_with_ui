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

            // Log the user in
            Auth::login($user);
            
            // Regenerate session
            session()->regenerate();

            // --- Google Drive Integration ---
            $googleToken = $googleUser->token;
            $googleRefreshToken = $googleUser->refreshToken ?? null;
            $expiresIn = $googleUser->expiresIn ?? null;
            $tokenArray = [
                'access_token' => $googleToken,
                'refresh_token' => $googleRefreshToken,
                'expires_in' => $expiresIn,
            ];
            $user->google_drive_token = json_encode($tokenArray);
            $user->google_drive_refresh_token = $googleRefreshToken;
            $user->google_drive_connected = true;
            // Create Google Drive folder if not exists
            if (!$user->google_drive_folder_id) {
                $driveService = app(\App\Services\GoogleDriveService::class);
                $driveService->setAccessToken($tokenArray);
                $folderName = "BUCS DocuManage - " . $user->name;
                $folderId = $driveService->createFolder($folderName);
                $user->google_drive_folder_id = $folderId;
            }
            $user->save();
            // --- End Google Drive Integration ---

            // Redirect to the dashboard page
            return redirect()->intended('/dashboard');

        } catch (\Exception $e) {
            \Log::error('Google authentication error: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'Something went wrong with Google authentication. Please try again.');
        }
    }
}