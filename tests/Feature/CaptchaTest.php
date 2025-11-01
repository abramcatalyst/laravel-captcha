<?php

namespace JustChill\LaravelCaptcha\Tests\Feature;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Config;
use JustChill\LaravelCaptcha\Services\CaptchaService;

class CaptchaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        Config::set('captcha.expires_minutes', 10);
        Config::set('captcha.max_attempts', 5);
        Config::set('captcha.type', 'math');
    }

    protected function getPackageProviders($app)
    {
        return [
            \JustChill\LaravelCaptcha\CaptchaServiceProvider::class,
        ];
    }

    public function test_captcha_validation_rule()
    {
        Config::set('captcha.type', 'math');
        
        $service = app('captcha');
        $challenge = $service->generate('math');
        
        session(['laravel_captcha' => [
            'answer' => $challenge['answer'],
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0
        ]]);
        
        $validator = \Illuminate\Support\Facades\Validator::make(
            ['captcha' => $challenge['answer']],
            ['captcha' => 'required|captcha']
        );
        
        $this->assertTrue($validator->passes());
    }

    public function test_captcha_validation_rule_fails()
    {
        $service = app('captcha');
        $challenge = $service->generate('math');
        
        session(['laravel_captcha' => [
            'answer' => $challenge['answer'],
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0
        ]]);
        
        $validator = \Illuminate\Support\Facades\Validator::make(
            ['captcha' => 'wrong_answer'],
            ['captcha' => 'required|captcha']
        );
        
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('captcha', $validator->errors()->toArray());
    }

    public function test_blade_directive_renders()
    {
        $service = app('captcha');
        $output = $service->render('math');
        
        $this->assertIsString($output);
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('captcha', strtolower($output));
    }

    public function test_captcha_service_is_singleton()
    {
        $service1 = app('captcha');
        $service2 = app('captcha');
        
        $this->assertSame($service1, $service2);
    }
}

