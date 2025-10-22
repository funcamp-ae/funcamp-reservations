<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
     * Link Google account to current user
     */
    public function linkGoogleAccount()
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors(['error' => 'You must be logged in to link your Google account.']);
        }

        // Store the current user ID in the session so we can retrieve it in the callback
        session(['linking_user_id' => Auth::id()]);

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google account linking callback
     */
    public function handleGoogleLinkCallback()
    {
        try {
            // Get the user ID from session (stored during the link initiation)
            $linkingUserId = session('linking_user_id');
            
            // Check if we have a linking user ID or if the user is still authenticated
            if (!$linkingUserId && !Auth::check()) {
                session()->forget('linking_user_id');
                return redirect('/login')->withErrors(['error' => 'Session expired. Please try again.']);
            }

            // Get the current user (either from session or current auth)
            $currentUser = $linkingUserId ? User::find($linkingUserId) : Auth::user();
            
            if (!$currentUser) {
                session()->forget('linking_user_id');
                return redirect('/login')->withErrors(['error' => 'User not found. Please try again.']);
            }

            // Get the Google user data
            $googleUser = Socialite::driver('google')->user();

            // Validate that we got the Google user data
            if (!$googleUser || !$googleUser->id) {
                session()->forget('linking_user_id');
                return redirect()->route('edit-profile')
                    ->withErrors(['google' => 'Failed to retrieve Google account information.']);
            }

            // Check if Google account is already linked to another user
            $existingUser = User::where('google_id', $googleUser->id)
                                ->where('id', '!=', $currentUser->id)
                                ->first();

            if ($existingUser) {
                session()->forget('linking_user_id');
                return redirect()->route('edit-profile')
                    ->withErrors(['google' => 'This Google account is already linked to another user.']);
            }

            // Link Google account to current user
            $currentUser->update([
                'google_id' => $googleUser->id,
                'google_token' => $googleUser->token,
                'google_refresh_token' => $googleUser->refreshToken,
            ]);

            // Clear the session data
            session()->forget('linking_user_id');

            // Ensure the user is logged in
            if (!Auth::check()) {
                Auth::login($currentUser);
            }

            return redirect()->route('edit-profile')
                ->with('success', 'Google account linked successfully!');

        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            Log::error('Google OAuth Invalid State Exception: ' . $e->getMessage());
            session()->forget('linking_user_id');
            return redirect()->route('edit-profile')
                ->withErrors(['google' => 'Invalid OAuth state. Please try again.']);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::error('Google OAuth Client Exception: ' . $e->getMessage());
            session()->forget('linking_user_id');
            return redirect()->route('edit-profile')
                ->withErrors(['google' => 'Failed to communicate with Google. Please try again.']);
        } catch (Exception $e) {
            Log::error('Google OAuth Exception: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            session()->forget('linking_user_id');
            return redirect()->route('edit-profile')
                ->withErrors(['google' => 'Something went wrong with Google account linking: ' . $e->getMessage()]);
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