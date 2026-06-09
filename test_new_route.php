<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = Modules\User\Models\User::role('employee')->first();
Illuminate\Support\Facades\Auth::login($user);
$session = app('session')->driver();

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/activity-logs', 'GET', [], [], [], ['HTTP_HOST' => '127.0.0.1:8000']);
$request->setLaravelSession($session);

try {
    $response = $kernel->handle($request);
    echo "GET /activity-logs\n";
    echo "Status: " . $response->getStatusCode() . "\n";

    if ($response->getStatusCode() === 200) {
        echo "✓ SUCCESS - Page loaded!\n";
        echo "Content length: " . strlen($response->getContent()) . " bytes\n";
    } else {
        // Try to extract the actual error
        $content = $response->getContent();
        if (str_contains($content, 'class="exception-message"')) {
            preg_match('/<h1[^>]*>([^<]+)<\/h1>/', $content, $m);
            echo "Error: " . ($m[1] ?? 'Unknown') . "\n";
        } elseif (str_contains($content, 'Exception')) {
            echo "Response contains exception\n";
            // Search for the exception message
            if (preg_match('/>(.*Exception.*?)</', $content, $m)) {
                echo "Exception: " . strip_tags($m[1]) . "\n";
            }
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    if ($prev = $e->getPrevious()) {
        echo "Previous: " . $prev->getMessage() . "\n";
    }
}

$kernel->terminate($request, $response);
