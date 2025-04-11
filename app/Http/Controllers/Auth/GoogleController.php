<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
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
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt(uniqid()), // Random password since we're using Google auth
                ]);
            } else {
                // Update user's Google ID if it's not set
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
            }

            // Log the user in
            Auth::login($user, true);
            
            // Regenerate session
            session()->regenerate();

            // Redirect to home route
            return redirect()->route('home');

        } catch (\Exception $e) {
            \Log::error('Google authentication error: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'Something went wrong with Google authentication. Please try again.');
        }
    }
} 