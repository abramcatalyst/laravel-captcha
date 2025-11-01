<?php

namespace JustChill\LaravelCaptcha\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CaptchaMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('post')) {
            $captchaValue = $request->input('captcha');
            
            if (empty($captchaValue)) {
                Log::debug('CAPTCHA: Missing value in request');
                return $this->invalidResponse($request);
            }

            if (!app('captcha')->validate($captchaValue)) {
                Log::info('CAPTCHA: Validation failed', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                return $this->invalidResponse($request);
            }
        }
        
        return $next($request);
    }

    /**
     * Return response for invalid CAPTCHA.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function invalidResponse(Request $request)
    {
        $errorMessage = trans('captcha::validation.captcha', [], config('app.locale', 'en'));
        
        // Fallback if translation not found
        if ($errorMessage === 'captcha::validation.captcha') {
            $errorMessage = 'The CAPTCHA is incorrect.';
        }
        
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $errorMessage,
                'errors' => ['captcha' => [$errorMessage]]
            ], 422);
        }

        return back()
            ->withErrors(['captcha' => $errorMessage])
            ->withInput($request->except('captcha'));
    }
}
