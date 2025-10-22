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
     * Handle Google OAuth callback - handles both login and account linking
     */
    public function handleGoogleCallback()
    {
        try {
            // Get the Google user data
            $googleUser = Socialite::driver('google')->user();

            // Validate that we got the Google user data
            if (!$googleUser || !$googleUser->id) {
                return redirect('/login')
                    ->withErrors(['error' => 'Failed to retrieve Google account information.']);
            }

            // Check if this is a linking request (user was already logged in)
            $linkingUserId = session('linking_user_id');
            
            if ($linkingUserId) {
                // This is an account linking request
                return $this->handleAccountLinking($googleUser, $linkingUserId);
            } else {
                // This is a regular Google login request
                return $this->handleGoogleLogin($googleUser);
            }

        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            Log::error('Google OAuth Invalid State Exception: ' . $e->getMessage());
            session()->forget('linking_user_id');
            return redirect('/login')
                ->withErrors(['error' => 'Invalid OAuth state. Please try again.']);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::error('Google OAuth Client Exception: ' . $e->getMessage());
            session()->forget('linking_user_id');
            return redirect('/login')
                ->withErrors(['error' => 'Failed to communicate with Google. Please try again.']);
        } catch (Exception $e) {
            Log::error('Google OAuth Exception: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            session()->forget('linking_user_id');
            return redirect('/login')
                ->withErrors(['error' => 'Something went wrong with Google authentication: ' . $e->getMessage()]);
        }
    }

    /**
     * Handle regular Google login
     */
    private function handleGoogleLogin($googleUser)
    {
        // Check if user already exists with this Google ID
        $existingUser = User::where('google_id', $googleUser->id)->first();

        if ($existingUser) {
            // User exists, update their Google token and log them in
            $existingUser->update([
                'google_token' => $googleUser->token,
                'google_refresh_token' => $googleUser->refreshToken,
            ]);

            Auth::login($existingUser);
            return redirect()->intended('/home');
        }else if ($userWithEmail = User::where('email', $googleUser->email)->first()) {
            // User exists with same email but no Google ID - link the account
            $userWithEmail->update([
                'google_id' => $googleUser->id,
                'google_token' => $googleUser->token,
                'google_refresh_token' => $googleUser->refreshToken,
            ]);

            Auth::login($userWithEmail);
            return redirect()->intended('/home');
        }

        else {
            // User does not exist, proceed to registration or linking
            return redirect('/login')->withErrors(['error' => 'You must link your Google account first.']);
        }
    }

    /**
     * Handle account linking for already logged-in users
     */
    private function handleAccountLinking($googleUser, $linkingUserId)
    {
        // Get the current user who wants to link their account
        $currentUser = User::find($linkingUserId);
        
        if (!$currentUser) {
            session()->forget('linking_user_id');
            return redirect('/login')->withErrors(['error' => 'User not found. Please try again.']);
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
    }

    /**
     * Unlink Google account from current user
     */
    public function unlinkGoogleAccount(Request $request)
    {
        if (!Auth::check()) {
            if ($request->ajax()) {
                return response()->json(['error' => 'You must be logged in.'], 401);
            }
            return redirect('/login');
        }

        try {
            $user = Auth::user();
            $user->update([
                'google_id' => null,
                'google_token' => null,
                'google_refresh_token' => null,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Google account unlinked successfully!'
                ]);
            }

            return redirect()->route('edit-profile')
                ->with('success', 'Google account unlinked successfully!');

        } catch (Exception $e) {
            Log::error('Error unlinking Google account: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'error' => 'Failed to unlink Google account. Please try again.'
                ], 500);
            }

            return redirect()->route('edit-profile')
                ->withErrors(['google' => 'Failed to unlink Google account. Please try again.']);
        }
    }
}