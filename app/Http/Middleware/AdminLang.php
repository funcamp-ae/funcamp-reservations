<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;

class AdminLang
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
       
        $lang = defaultLang();

        if (Session::has('lang') && in_array(Session::get('lang'), languages())) {
            $lang = Session::get('lang');
        }
        if (str_contains($request->url(), 'registrationforms/create')) {
            if (Session::has('registrationforms-lang')) {
                $registrationLang = Session::get('registrationforms-lang');
                $lang = str_contains($registrationLang, 'ar') ? 'ar' : 'en';
            }
        } 
        App::setLocale($lang);
        Carbon::setLocale($lang);
        return $next($request);
    
    }
}
