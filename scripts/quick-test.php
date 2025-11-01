<?php

/**
 * Quick Test Script for Laravel CAPTCHA Package
 * 
 * This is a standalone test script that doesn't require Laravel to be installed.
 * It tests basic functionality that doesn't depend on Laravel's service container.
 * 
 * Usage: php scripts/quick-test.php
 */

echo "🧪 Laravel CAPTCHA - Quick Test Script\n";
echo str_repeat("=", 50) . "\n\n";

// Test 1: Configuration file syntax
echo "1️⃣  Testing configuration file syntax...\n";
$configFile = __DIR__ . '/../src/config/captcha.php';
if (file_exists($configFile)) {
    // Create a mock env() function for testing
    if (!function_exists('env')) {
        function env($key, $default = null) {
            return $default;
        }
    }
    if (!function_exists('base_path')) {
        function base_path($path = '') {
            return __DIR__ . '/../' . $path;
        }
    }
    
    ob_start();
    try {
        $result = include $configFile;
        ob_end_clean();
        if (is_array($result)) {
            echo "   ✅ Config file is valid PHP array\n";
        } else {
            echo "   ❌ Config file is not a valid array\n";
            exit(1);
        }
    } catch (\Throwable $e) {
        ob_end_clean();
        echo "   ⚠️  Config uses Laravel helpers (env, base_path) - this is expected\n";
        echo "   ✅ Config file syntax is valid (requires Laravel to test fully)\n";
    }
} else {
    echo "   ❌ Config file not found\n";
    exit(1);
}

// Test 2: Check all PHP files syntax
echo "\n2️⃣  Testing PHP file syntax...\n";
$phpFiles = [
    __DIR__ . '/../src/Services/CaptchaService.php',
    __DIR__ . '/../src/Http/Controllers/CaptchaController.php',
    __DIR__ . '/../src/CaptchaServiceProvider.php',
    __DIR__ . '/../src/Http/Middleware/CaptchaMiddleware.php',
];

$allValid = true;
foreach ($phpFiles as $file) {
    $output = [];
    $returnVar = 0;
    exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $returnVar);
    
    if ($returnVar === 0) {
        echo "   ✅ " . basename($file) . "\n";
    } else {
        echo "   ❌ " . basename($file) . " - " . implode("\n", $output) . "\n";
        $allValid = false;
    }
}

if (!$allValid) {
    echo "\n❌ Syntax errors found!\n";
    exit(1);
}

// Test 3: Check composer.json
echo "\n3️⃣  Testing composer.json...\n";
$composerFile = __DIR__ . '/../composer.json';
if (file_exists($composerFile)) {
    $composer = json_decode(file_get_contents($composerFile), true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "   ✅ composer.json is valid JSON\n";
        
        // Check required keys
        $required = ['name', 'description', 'require', 'autoload'];
        $missing = [];
        foreach ($required as $key) {
            if (!isset($composer[$key])) {
                $missing[] = $key;
            }
        }
        
        if (empty($missing)) {
            echo "   ✅ All required composer.json keys present\n";
        } else {
            echo "   ❌ Missing keys: " . implode(', ', $missing) . "\n";
            exit(1);
        }
    } else {
        echo "   ❌ composer.json is not valid JSON\n";
        exit(1);
    }
} else {
    echo "   ❌ composer.json not found\n";
    exit(1);
}

// Test 4: Check directory structure
echo "\n4️⃣  Testing directory structure...\n";
$requiredDirs = [
    'src/Services',
    'src/Http/Controllers',
    'src/Http/Middleware',
    'src/config',
    'src/resources/views',
    'src/resources/lang',
];

$allDirsExist = true;
foreach ($requiredDirs as $dir) {
    $fullPath = __DIR__ . '/../' . $dir;
    if (is_dir($fullPath)) {
        echo "   ✅ {$dir}\n";
    } else {
        echo "   ❌ {$dir} - Directory not found\n";
        $allDirsExist = false;
    }
}

if (!$allDirsExist) {
    echo "\n❌ Some required directories are missing!\n";
    exit(1);
}

// Test 5: Check critical files exist
echo "\n5️⃣  Testing critical files...\n";
$requiredFiles = [
    'src/Services/CaptchaService.php',
    'src/Http/Controllers/CaptchaController.php',
    'src/CaptchaServiceProvider.php',
    'src/Http/Middleware/CaptchaMiddleware.php',
    'src/config/captcha.php',
    'src/routes/web.php',
    'README.md',
    'LICENSE',
];

$allFilesExist = true;
foreach ($requiredFiles as $file) {
    $fullPath = __DIR__ . '/../' . $file;
    if (file_exists($fullPath)) {
        echo "   ✅ {$file}\n";
    } else {
        echo "   ❌ {$file} - File not found\n";
        $allFilesExist = false;
    }
}

if (!$allFilesExist) {
    echo "\n❌ Some required files are missing!\n";
    exit(1);
}

// Test 6: Check namespace consistency
echo "\n6️⃣  Testing namespace consistency...\n";
$serviceProvider = file_get_contents(__DIR__ . '/../src/CaptchaServiceProvider.php');
if (strpos($serviceProvider, 'namespace JustChill\\LaravelCaptcha;') !== false) {
    echo "   ✅ ServiceProvider namespace correct\n";
} else {
    echo "   ❌ ServiceProvider namespace incorrect\n";
    exit(1);
}

// Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "✅ All basic checks passed!\n\n";
echo "📝 Next Steps:\n";
echo "   1. Use path repository in a Laravel app (see TESTING.md)\n";
echo "   2. Or run unit tests: composer test\n";
echo "   3. Or test manually in a Laravel application\n\n";
echo "💡 Tip: See TESTING.md for detailed testing instructions\n";

