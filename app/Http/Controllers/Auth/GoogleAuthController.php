<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Check if user already exists
            $user = User::where('email', $googleUser->email)->first();
            
            if ($user) {
                // Update Google ID if not already set
                if (empty($user->google_id)) {
                    $user->update([
                        'google_id' => $googleUser->id,
                        'google_token' => $googleUser->token,
                        'google_refresh_token' => $googleUser->refreshToken,
                    ]);
                }
                
                Auth::login($user);
                return redirect()->intended('/home');
            } else {
                // Create new user (if you want to allow registration through Google)
                // For admin-only access, you might want to disable this
                return redirect('/login')->withErrors(['email' => 'No account found with this Google email.']);
            }
            
        } catch (Exception $e) {
            return redirect('/login')->withErrors(['email' => 'Something went wrong with Google authentication.']);
        }
    }

    /**
     * Link Google account to current user
     */
    public function linkGoogleAccount()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google account linking callback
     */
    public function handleGoogleLinkCallback()
    {
        try {
            if (!Auth::check()) {
                return redirect('/login');
            }

            $googleUser = Socialite::driver('google')->user();
            $currentUser = Auth::user();

            // Check if Google account is already linked to another user
            $existingUser = User::where('google_id', $googleUser->id)
                                ->where('id', '!=', $currentUser->id)
                                ->first();

            if ($existingUser) {
                return redirect()->route('edit-profile')
                    ->withErrors(['google' => 'This Google account is already linked to another user.']);
            }

            // Link Google account to current user
            $currentUser->update([
                'google_id' => $googleUser->id,
                'google_token' => $googleUser->token,
                'google_refresh_token' => $googleUser->refreshToken,
            ]);

            return redirect()->route('edit-profile')
                ->with('success', 'Google account linked successfully!');

        } catch (Exception $e) {
            return redirect()->route('edit-profile')
                ->withErrors(['google' => 'Something went wrong with Google account linking.']);
        }
    }

    /**
     * Unlink Google account from current user
     */
    public function unlinkGoogleAccount()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        $user->update([
            'google_id' => null,
            'google_token' => null,
            'google_refresh_token' => null,
        ]);

        return redirect()->route('edit-profile')
            ->with('success', 'Google account unlinked successfully!');
    }
}