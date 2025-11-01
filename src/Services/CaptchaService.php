<?php

namespace JustChill\LaravelCaptcha\Services;

use Illuminate\Support\Str;

class CaptchaService
{
    protected $sessionKey = 'laravel_captcha';

    public function generate(?string $type = null)
    {
        $type = $type ?? config('captcha.type', 'math');
        $challenge = $this->createChallenge($type);
        session([$this->sessionKey => [
            'answer' => $challenge['answer'],
            'expires_at' => now()->addMinutes(config('captcha.expires_minutes', 10)),
            'attempts' => 0
        ]]);

        return $challenge;
    }

    public function validate($userAnswer)
    {
        // Sanitize input
        if (!is_string($userAnswer)) {
            $userAnswer = (string) $userAnswer;
        }
        $userAnswer = trim($userAnswer);

        $captcha = session($this->sessionKey);

        if (!$captcha || !isset($captcha['answer']) || !isset($captcha['expires_at'])) {
            return false;
        }

        // Check expiration
        $expiresAt = $captcha['expires_at'];
        if (!($expiresAt instanceof \Illuminate\Support\Carbon || $expiresAt instanceof \Carbon\Carbon)) {
            // Handle string dates
            try {
                $expiresAt = \Illuminate\Support\Carbon::parse($expiresAt);
            } catch (\Exception $e) {
                session()->forget($this->sessionKey);
                return false;
            }
        }

        if (now()->greaterThan($expiresAt)) {
            session()->forget($this->sessionKey);
            return false;
        }

        // Check max attempts
        $maxAttempts = config('captcha.max_attempts', 5);
        $attempts = $captcha['attempts'] ?? 0;
        
        if ($attempts >= $maxAttempts) {
            session()->forget($this->sessionKey);
            return false;
        }

        // Increment attempts before validation
        session([
            $this->sessionKey . '.attempts' => $attempts + 1
        ]);

        // Case-sensitive validation (configurable)
        $caseSensitive = config('captcha.case_sensitive', true);
        $correctAnswer = trim($captcha['answer']);
        
        if ($caseSensitive) {
            $isValid = $userAnswer === $correctAnswer;
        } else {
            $isValid = strtolower($userAnswer) === strtolower($correctAnswer);
        }

        // Clear session on successful validation
        if ($isValid) {
            session()->forget($this->sessionKey);
        }

        return $isValid;
    }

    public function render(?string $type = null)
    {
        $type = $type ?? config('captcha.type', 'math');
        $challenge = $this->generate($type);

        return view('captcha::challenge', [
            'challenge' => $challenge,
            'hasGD' => extension_loaded('gd')
        ])->render();
    }


    protected function createChallenge($type)
    {
        switch ($type) {
            case 'math':
                return $this->mathChallenge();
            case 'word':
                return $this->wordChallenge();
            case 'image':
                return $this->imageChallenge();
            default:
                return $this->mathChallenge();
        }
    }

    protected function mathChallenge()
    {
        $operations = ['+', '-', '*'];
        $operation = $operations[array_rand($operations)];

        switch ($operation) {
            case '+':
                $a = rand(1, 20);
                $b = rand(1, 20);
                $answer = $a + $b;
                $question = "What is {$a} + {$b}?";
                break;
            case '-':
                $a = rand(10, 30);
                $b = rand(1, $a);
                $answer = $a - $b;
                $question = "What is {$a} - {$b}?";
                break;
            case '*':
                $a = rand(1, 10);
                $b = rand(1, 10);
                $answer = $a * $b;
                $question = "What is {$a} × {$b}?";
                break;
        }

        return [
            'type' => 'math',
            'question' => $question,
            'answer' => (string) $answer
        ];
    }

    protected function generateRandomWord(int $length = 6): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz';
        $word = '';

        for ($i = 0; $i < $length; $i++) {
            $char = $characters[rand(0, 25)];

            // Randomly upper or lowercase it
            $word .= rand(0, 1) ? strtoupper($char) : $char;
        }

        return $word;
    }

    protected function wordChallenge()
    {
        $length = config('captcha.length.word', 6);
        $word = $this->generateRandomWord($length);

        return [
            'type' => 'word',
            'question' => "Type the word: <strong>{$word}</strong>",
            'answer' => $word
        ];
    }

    protected function imageChallenge()
    {
        if (!extension_loaded('gd')) {
            // Fallback to math challenge with a warning
            $mathChallenge = $this->mathChallenge();
            $mathChallenge['warning'] = 'Image CAPTCHA requires GD extension. Please install php-gd or use text-based CAPTCHA.';
            return $mathChallenge;
        }
        $length = config('captcha.length.image', 5);
        $code = Str::random($length);
        return [
            'type' => 'image',
            'question' => 'Enter the code shown in the image',
            'answer' => $code,
            'image_url' => route('captcha.image', ['code' => encrypt($code)])
        ];
    }

    public function generateImage(string $code): \Illuminate\Http\Response
    {
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('GD extension is required for image CAPTCHA generation.');
        }

        $width = config('captcha.image.width', 150);
        $height = config('captcha.image.height', 50);
        $fontSize = config('captcha.image.font_size', 24);
        $bgColor = config('captcha.image.bg_color', '#ffffff');
        $textColor = config('captcha.image.text_color', '#000000');
        $fonts = config('captcha.fonts', []);

        $image = imagecreatetruecolor($width, $height);
        
        if ($image === false) {
            throw new \RuntimeException('Failed to create image resource.');
        }

        // Convert hex colors to RGB
        [$r, $g, $b] = sscanf($bgColor, "#%02x%02x%02x");
        $bg = imagecolorallocate($image, $r ?? 255, $g ?? 255, $b ?? 255);
        imagefill($image, 0, 0, $bg);

        [$tr, $tg, $tb] = sscanf($textColor, "#%02x%02x%02x");
        $textColorAlloc = imagecolorallocate($image, $tr ?? 0, $tg ?? 0, $tb ?? 0);

        // Add noise lines
        if (config('captcha.image.noise', true)) {
            $lines = config('captcha.image.lines', 3);
            for ($i = 0; $i < $lines; $i++) {
                $lineColor = imagecolorallocate($image, rand(100, 255), rand(100, 255), rand(100, 255));
                imageline($image, 0, rand(0, $height), $width, rand(0, $height), $lineColor);
            }
        }

        // Draw text
        $font = null;
        if (!empty($fonts) && is_array($fonts)) {
            foreach ($fonts as $fontPath) {
                if ($fontPath && file_exists($fontPath)) {
                    $font = $fontPath;
                    break;
                }
            }
        }

        if ($font && function_exists('imagettftext')) {
            $box = imagettfbbox($fontSize, 0, $font, $code);
            if ($box !== false) {
                $x = ($width - ($box[2] - $box[0])) / 2;
                $y = ($height + ($box[1] - $box[7])) / 2;
                imagettftext($image, $fontSize, 0, (int)$x, (int)$y, $textColorAlloc, $font, $code);
            } else {
                imagestring($image, 5, 10, 10, $code, $textColorAlloc);
            }
        } else {
            imagestring($image, 5, 10, 10, $code, $textColorAlloc);
        }

        // Capture output
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        if ($imageData === false) {
            throw new \RuntimeException('Failed to generate image data.');
        }

        return response($imageData)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
