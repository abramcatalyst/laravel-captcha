<?php

namespace JustChill\LaravelCaptcha\Tests\Unit;

use JustChill\LaravelCaptcha\Services\CaptchaService;
use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Config;

class CaptchaServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        Config::set('captcha.expires_minutes', 10);
        Config::set('captcha.max_attempts', 5);
        Config::set('captcha.case_sensitive', true);
        Config::set('captcha.type', 'math');
    }

    protected function getPackageProviders($app)
    {
        return [
            \JustChill\LaravelCaptcha\CaptchaServiceProvider::class,
        ];
    }

    public function test_generate_math_challenge()
    {
        Config::set('captcha.type', 'math');
        
        $service = new CaptchaService();
        $challenge = $service->generate('math');
        
        $this->assertIsArray($challenge);
        $this->assertEquals('math', $challenge['type']);
        $this->assertArrayHasKey('question', $challenge);
        $this->assertArrayHasKey('answer', $challenge);
        $this->assertNotEmpty($challenge['answer']);
    }

    public function test_generate_word_challenge()
    {
        Config::set('captcha.type', 'word');
        Config::set('captcha.length.word', 6);
        
        $service = new CaptchaService();
        $challenge = $service->generate('word');
        
        $this->assertIsArray($challenge);
        $this->assertEquals('word', $challenge['type']);
        $this->assertArrayHasKey('question', $challenge);
        $this->assertArrayHasKey('answer', $challenge);
        $this->assertEquals(6, strlen($challenge['answer']));
    }

    public function test_validate_correct_answer()
    {
        $service = new CaptchaService();
        $challenge = $service->generate('math');
        
        session(['laravel_captcha' => [
            'answer' => $challenge['answer'],
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0
        ]]);
        
        $isValid = $service->validate($challenge['answer']);
        
        $this->assertTrue($isValid);
        $this->assertNull(session('laravel_captcha'));
    }

    public function test_validate_incorrect_answer()
    {
        $service = new CaptchaService();
        $challenge = $service->generate('math');
        
        session(['laravel_captcha' => [
            'answer' => $challenge['answer'],
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0
        ]]);
        
        $isValid = $service->validate('wrong_answer');
        
        $this->assertFalse($isValid);
    }

    public function test_validate_expired_captcha()
    {
        $service = new CaptchaService();
        
        session(['laravel_captcha' => [
            'answer' => '123',
            'expires_at' => now()->subMinutes(1), // Expired
            'attempts' => 0
        ]]);
        
        $isValid = $service->validate('123');
        
        $this->assertFalse($isValid);
    }

    public function test_validate_max_attempts_exceeded()
    {
        Config::set('captcha.max_attempts', 3);
        
        $service = new CaptchaService();
        
        session(['laravel_captcha' => [
            'answer' => '123',
            'expires_at' => now()->addMinutes(10),
            'attempts' => 3 // Max attempts reached
        ]]);
        
        $isValid = $service->validate('123');
        
        $this->assertFalse($isValid);
    }

    public function test_case_sensitive_validation()
    {
        Config::set('captcha.case_sensitive', true);
        
        $service = new CaptchaService();
        
        session(['laravel_captcha' => [
            'answer' => 'Test123',
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0
        ]]);
        
        $this->assertTrue($service->validate('Test123'));
        
        session(['laravel_captcha' => [
            'answer' => 'Test123',
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0
        ]]);
        
        $this->assertFalse($service->validate('test123'));
    }

    public function test_case_insensitive_validation()
    {
        Config::set('captcha.case_sensitive', false);
        
        $service = new CaptchaService();
        
        session(['laravel_captcha' => [
            'answer' => 'Test123',
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0
        ]]);
        
        $this->assertTrue($service->validate('test123'));
        $this->assertTrue($service->validate('TEST123'));
    }
}

