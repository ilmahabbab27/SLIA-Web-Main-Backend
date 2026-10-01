<?php

// Upload beside the public Laravel index.php, set these values, then delete after testing.
$testKey = '1b9cb108aee04f3798a0a2b0d5e03a51';
$recipient = 'ilmahabbab27@gmail.com';
// Optional: set the absolute server path to the folder containing artisan.
// Leave empty to check this folder and common Laravel deployment locations.
$laravelRoot = '';

ini_set('display_errors', '0');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');
header('Content-Type: text/html; charset=UTF-8');

function escapeMailTest($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

if ($testKey === 'CHANGE_THIS_TO_A_LONG_RANDOM_SECRET' || $recipient === 'YOUR_EMAIL@example.com') {
    http_response_code(503);
    exit('Edit the test key and recipient in this file before using it.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<h1>SLIA email test</h1><form method="post">';
    echo '<label>Test key <input type="password" name="key" required autocomplete="off"></label> ';
    echo '<button type="submit">Send test email</button></form>';
    exit;
}

$providedKey = $_POST['key'] ?? '';
if (!is_string($providedKey) || !hash_equals($testKey, $providedKey)) {
    http_response_code(403);
    exit('Invalid test key.');
}

try {
    if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Set a valid recipient email in this file.');
    }
    $candidates = $laravelRoot !== '' ? [$laravelRoot] : [
        __DIR__,
        dirname(__DIR__),
        __DIR__ . '/backend',
        __DIR__ . '/laravel',
        dirname(__DIR__) . '/backend',
        dirname(__DIR__) . '/laravel',
    ];
    $laravelRoot = '';
    foreach ($candidates as $candidate) {
        if (is_file($candidate . '/vendor/autoload.php') && is_file($candidate . '/bootstrap/app.php')) {
            $laravelRoot = $candidate;
            break;
        }
    }
    if ($laravelRoot === '') {
        throw new RuntimeException(
            'Laravel files were not found. In hosting File Manager, locate artisan and set $laravelRoot to its folder path. '
            . 'The test file is in: ' . __DIR__
        );
    }

    require $laravelRoot . '/vendor/autoload.php';
    $app = require $laravelRoot . '/bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    echo '<p>Active mailer: ' . escapeMailTest(config('mail.default')) . '</p>';
    echo '<p>From: ' . escapeMailTest(config('mail.from.address')) . '</p>';
    echo '<p>Configuration cached: ' . ($app->configurationIsCached() ? 'yes' : 'no') . '</p>';

    Illuminate\Support\Facades\Mail::raw(
        'This is a SLIA production email test using the same Laravel mail configuration as member registration.',
        function ($message) use ($recipient) {
            $message->to($recipient)->subject('SLIA Production Email Test');
        }
    );

    echo '<p>The configured mail transport returned without an exception. This does not confirm inbox delivery. Check your inbox and Spam folder. Log/array mailers do not deliver email.</p>';
} catch (Throwable $error) {
    http_response_code(500);
    echo '<p>Email test failed:</p><pre>' . escapeMailTest($error->getMessage()) . '</pre>';
}

echo '<p>Delete this file from the server after testing.</p>';
