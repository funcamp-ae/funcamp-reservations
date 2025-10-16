<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Helpers\TranslationHelper;

class TranslationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register @translate directive
        Blade::directive('translate', function ($expression) {
            // Remove parentheses and split the expression
            $expression = trim($expression, "()'\" ");
            return "<?php echo translateText($expression, session('lang', 'ps')); ?>";
        });
    }
}
