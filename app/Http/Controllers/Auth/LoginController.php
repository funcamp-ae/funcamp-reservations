<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }


    public function showLoginForm()
    {
        return view('auth.login'); // create a view for the login form
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required',
            'remember_me' => 'sometimes|boolean',
        ]);

        // Determine if the input is email or phone
        $loginField = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        // Prepare credentials
        $credentials = [
            $loginField => $request->email,
            'password' => $request->password
        ];

        // Attempt login
        if (Auth::attempt($credentials, $request->filled('remember_me'))) {
            $user = Auth::user();
            session()->put('lang', $user->lang);
            return redirect()->route('home');
        }

        return redirect()->back()
            ->withInput($request->only('email'))
            ->with('error', 'البريد الإلكتروني أو رقم الهاتف أو كلمة المرور غير صحيحة');
    }

}

