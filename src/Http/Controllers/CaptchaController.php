<?php

namespace JustChill\LaravelCaptcha\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use JustChill\LaravelCaptcha\Services\CaptchaService;
use Illuminate\Support\Facades\Log;

class CaptchaController extends Controller
{
    protected $captchaService;

    public function __construct(CaptchaService $captchaService)
    {
        $this->captchaService = $captchaService;
    }

    public function image(Request $request)
    {
        try {
            $encryptedCode = $request->get('code');
            
            if (!$encryptedCode) {
                return response('Missing code parameter', 400);
            }

            $code = decrypt($encryptedCode);

            // Store in session for validation
            session(['laravel_captcha' => [
                'answer' => $code,
                'expires_at' => now()->addMinutes(config('captcha.expires_minutes', 10)),
                'attempts' => 0
            ]]);

            return $this->captchaService->generateImage($code);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            Log::warning('CAPTCHA: Failed to decrypt code', ['error' => $e->getMessage()]);
            return response('Invalid CAPTCHA code', 400);
        } catch (\RuntimeException $e) {
            Log::error('CAPTCHA: Image generation failed', ['error' => $e->getMessage()]);
            return response('Image generation failed', 500);
        } catch (\Exception $e) {
            Log::error('CAPTCHA: Unexpected error', ['error' => $e->getMessage()]);
            return response('An error occurred', 500);
        }
    }
}
