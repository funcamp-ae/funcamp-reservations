<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Password;
use App\Mail\ResetPassword;
use Illuminate\Http\Request;
use Mail;
use Illuminate\Support\Str;
use App\Models\User;
use DB;
use Hash;
use Carbon\Carbon; 


class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
        ]);

        // Get the input (email or phone)
        $identifier = $request->input('email');
        
        // Determine if the input is email or phone
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);
        
        // If it's an email, use Laravel's Password facade
        if ($isEmail) {
            $status = Password::sendResetLink(
                $request->only('email')
            );
        
            return $status === Password::RESET_LINK_SENT
                ? back()->with('success', __($status))
                : back()->withErrors(['email' => __($status)]);
        } else {
            // For phone number, handle manually
            $user = User::where('phone', $identifier)->first();

            if ($user) {
                $token = Str::random(60);

                // Delete old tokens for this user
                DB::table('password_resets')
                    ->where('phone', $user->phone)
                    ->delete();
                    
                // Insert new token
                DB::table('password_resets')->insert([
                    'phone' => $user->phone,
                    'email' => null,
                    'token' => $token,
                    'created_at' => Carbon::now(),
                ]);

                $actionUrl = route('password.reset', ['token' => $token]);
        
                // Send email notification if user has email
                if ($user->email) {
                    Mail::to($user)->send(new ResetPassword($actionUrl));
                    return back()->with('success', 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني');
                } else {
                    // If user has no email, they can use the reset link directly
                    return back()->with('success', 'تم إنشاء رابط إعادة تعيين كلمة المرور. يرجى استخدام الرابط المرسل.');
                }
                
            } else {
               return back()->withErrors(['email' => 'رقم الهاتف غير موجود']);
            }
        }
    }


    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|string',
            'password' => 'required|confirmed|min:8', 
        ]);
        
        // Check if input is email
        $isEmail = filter_var($request->email, FILTER_VALIDATE_EMAIL);
        
        if ($isEmail) {
            // Use Laravel's Password facade for email
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    // Don't use Hash::make() here because setPasswordAttribute already hashes it
                    $user->password = $password;
                    $user->setRememberToken(Str::random(60));
                    $user->save();
                }
            );
         
            return $status === Password::PASSWORD_RESET
                ? redirect()->route('login')->with('success', 'تم تعديل كلمة المرور بنجاح')
                : back()->withErrors(['email' => [__($status)]]);
        } else {
            // Handle phone number reset manually
            $tokenData = DB::table('password_resets')
                ->where('token', $request->token)
                ->where('phone', $request->email)
                ->first();

            if ($tokenData) {
                $user = User::where('phone', $tokenData->phone)->first();
                
                if ($user) {
                    // Update password (setPasswordAttribute will hash it automatically)
                    $user->password = $request->password;
                    $user->setRememberToken(Str::random(60));
                    $user->save();
        
                    // Remove the token from password_resets table
                    DB::table('password_resets')->where('token', $request->token)->delete();
        
                    return redirect()->route('login')->with('success', "تم تعديل كلمة المرور بنجاح");
                } else {
                    return back()->withErrors(['email' => 'المستخدم غير موجود']);
                }
            } else {
                return back()->withErrors(['email' => 'رابط غير صالح أو منتهي الصلاحية']);
            }
        }
    }



}
