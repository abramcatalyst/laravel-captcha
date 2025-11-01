<?php

namespace JustChill\LaravelCaptcha;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Blade;
use JustChill\LaravelCaptcha\Services\CaptchaService;
use JustChill\LaravelCaptcha\Http\Middleware\CaptchaMiddleware;

class CaptchaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('captcha', function ($app) {
            return new CaptchaService();
        });
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'captcha');
        
        // Load translations
        // Path should point to where the language directories (en, es, etc.) are located
        $this->loadTranslationsFrom(__DIR__ . '/resources/lang/vendor/captcha', 'captcha');

        // Register middleware
        // For Laravel 9-10, use aliasMiddleware
        // For Laravel 11+, middleware can be referenced by class name directly
        if (method_exists($this->app['router'], 'aliasMiddleware')) {
            $this->app['router']->aliasMiddleware('captcha', CaptchaMiddleware::class);
        }

        // Register validation rule with custom message
        Validator::extend('captcha', function ($attribute, $value, $parameters, $validator) {
            return app('captcha')->validate($value);
        });
        
        // Set custom validation message
        Validator::replacer('captcha', function ($message, $attribute, $rule, $parameters) {
            $translation = trans('captcha::validation.captcha');
            // Fallback if translation not found
            return $translation !== 'captcha::validation.captcha' 
                ? $translation 
                : 'The CAPTCHA is incorrect.';
        });

        // Register Blade directive
        Blade::directive('captcha', function () {
            return "<?php echo app('captcha')->render(); ?>";
        });

        // Publish config and views if needed
        $this->publishes([
            __DIR__ . '/config/captcha.php' => config_path('captcha.php'),
        ], 'captcha-config');

        $this->publishes([
            __DIR__ . '/resources/views' => resource_path('views/vendor/captcha'),
        ], 'captcha-views');

        $this->publishes([
            __DIR__ . '/resources/lang' => lang_path('vendor/captcha'),
        ], 'captcha-lang');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
    }
}
