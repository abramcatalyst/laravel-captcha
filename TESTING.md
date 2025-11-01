# Testing Guide - Local Development

This guide shows you how to test the package locally **without pushing to GitHub**.

## 🚀 Option 1: Path Repository (Recommended - No GitHub Needed)

This is the fastest way to test locally using Composer's path repository.

### Step 1: Create or Use Existing Laravel App

If you don't have a test Laravel app:
```bash
# Create new Laravel app (if needed)
composer create-project laravel/laravel test-laravel-captcha
cd test-laravel-captcha
```

### Step 2: Add Path Repository

Add this to your Laravel app's `composer.json`:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../laravel-captcha",
      "options": {
        "symlink": true
      }
    }
  ],
  "require": {
    "justchill/laravel-captcha": "*"
  },
  "minimum-stability": "dev",
  "prefer-stable": true
}
```

**Important**: Adjust the path `../laravel-captcha` to match your actual directory structure.

### Step 3: Install Package

```bash
composer require justchill/laravel-captcha
```

This creates a symlink to your local package - changes in the package are immediately available!

### Step 4: Publish Config

```bash
php artisan vendor:publish --tag=captcha-config
```

### Step 5: Test It!

Create a test route in `routes/web.php`:

```php
use Illuminate\Support\Facades\Route;

Route::get('/captcha-test', function () {
    return view('captcha-test');
});

Route::post('/captcha-test', function (\Illuminate\Http\Request $request) {
    $valid = $request->validate([
        'captcha' => 'required|captcha'
    ]);
    
    return redirect('/captcha-test')->with('success', 'CAPTCHA validated!');
});
```

Create `resources/views/captcha-test.blade.php`:

```blade
<!DOCTYPE html>
<html>
<head>
    <title>CAPTCHA Test</title>
    <style>
        body { font-family: Arial; max-width: 500px; margin: 50px auto; }
        .success { color: green; padding: 10px; background: #d4edda; border-radius: 5px; }
        .error { color: red; padding: 10px; background: #f8d7da; border-radius: 5px; }
        input[type="text"] { padding: 8px; width: 200px; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>CAPTCHA Package Test</h1>
    
    @if(session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif
    
    @if($errors->any())
        <div class="error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif
    
    <form method="POST" action="/captcha-test">
        @csrf
        <div style="margin: 20px 0;">
            @captcha
        </div>
        <button type="submit">Test CAPTCHA</button>
    </form>
</body>
</html>
```

### Step 6: Test Different Types

Update your `.env`:
```env
CAPTCHA_TYPE=math
# Then test: CAPTCHA_TYPE=word
# Then test: CAPTCHA_TYPE=image (requires GD)
```

---

## 🧪 Option 2: Unit Tests with Orchestra Testbench

For automated testing (requires dev dependencies):

```bash
# In the package directory
composer require --dev orchestra/testbench phpunit/phpunit

# Run tests
composer test
# OR
./vendor/bin/phpunit
```

---

## 🔄 Option 3: Install from Dev Branch (After Pushing)

If you want to test from GitHub:

```bash
composer require justchill/laravel-captcha:dev-dev
```

But **you don't need to do this** - Option 1 is better for local testing.

---

## ✅ Quick Test Checklist

After setup, test these scenarios:

### Math CAPTCHA
- [ ] Form displays math question
- [ ] Correct answer validates
- [ ] Wrong answer shows error
- [ ] Can submit multiple times (new CAPTCHA each time)

### Word CAPTCHA
- [ ] Set `CAPTCHA_TYPE=word` in `.env`
- [ ] Form displays word challenge
- [ ] Test case-sensitive (default)
- [ ] Set `CAPTCHA_CASE_SENSITIVE=false` and test case-insensitive

### Configuration
- [ ] Set `CAPTCHA_EXPIRES_MINUTES=2` and wait 2+ minutes → should expire
- [ ] Set `CAPTCHA_MAX_ATTEMPTS=2` → should block after 2 wrong attempts

### Middleware
```php
Route::post('/test-middleware', function() {
    return 'Success';
})->middleware('captcha');
```
- [ ] Without CAPTCHA → should redirect with error
- [ ] With wrong CAPTCHA → should redirect with error
- [ ] With correct CAPTCHA → should return success

---

## 🐛 Troubleshooting

### "Class not found" errors
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Changes not reflecting
```bash
# Re-link the package
composer update justchill/laravel-captcha
```

### Session issues
Make sure your `.env` has:
```env
SESSION_DRIVER=file
```

---

## 📝 Quick Test Script

If you want to quickly test in tinker:

```bash
php artisan tinker
```

```php
// Generate a challenge
$challenge = app('captcha')->generate('math');
$challenge['question']; // Shows the question
$challenge['answer']; // The correct answer

// Validate (use the answer from above)
app('captcha')->validate('22'); // Replace with actual answer

// Test different types
app('captcha')->generate('word');
app('captcha')->generate('image'); // Requires GD
```

---

## 💡 Pro Tip

Using path repository with symlink (`"symlink": true`) means:
- ✅ Changes in your package are immediately available
- ✅ No need to reinstall after edits
- ✅ Faster development cycle
- ✅ Can still push to GitHub when ready

Just edit files in `laravel-captcha` directory and refresh your test app!

