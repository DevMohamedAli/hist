<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Modules\User\Models\User;

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

// Boot the application fully
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get a logged-in employee
$user = User::role('employee')->first();

if (! $user) {
    echo "ERROR: No employee user found!\n";
    exit(1);
}

echo "User: {$user->email}\n";
echo 'Roles: '.implode(', ', $user->getRoleNames()->all())."\n";
echo 'Gate check (viewActivityLogUi): ';

// Test the gate
$allowed = Gate::forUser($user)->allows('viewActivityLogUi');
echo ($allowed ? '✓ YES' : '✗ NO')."\n";

// Also test authorize directly
try {
    Gate::forUser($user)->authorize('viewActivityLogUi');
    echo "Gate::authorize: ✓ Passed\n";
} catch (Exception $e) {
    echo 'Gate::authorize: ✗ Failed - '.$e->getMessage()."\n";
}

// Try to test the middleware behavior
echo "\n=== Testing Package UI Access ===\n";
$session = app('session')->driver();
Auth::login($user);

$request = Request::create('/activitylog-ui', 'GET', [], [], [], ['HTTP_HOST' => '127.0.0.1:8000']);
$request->setLaravelSession($session);

$kernel = $app->make(Kernel::class);
$response = $kernel->handle($request);

echo 'GET /activitylog-ui: '.$response->getStatusCode()."\n";
if ($response->getStatusCode() === 403) {
    echo 'Content: '.substr($response->getContent(), 0, 500)."\n";
}

// Now test the API endpoint
$apiRequest = Request::create('/activitylog-ui/api/activities?per_page=25', 'GET', [], [], [], ['HTTP_HOST' => '127.0.0.1:8000', 'HTTP_ACCEPT' => 'application/json']);
$apiRequest->setLaravelSession($session);
$apiResponse = $kernel->handle($apiRequest);

echo 'GET /activitylog-ui/api/activities: '.$apiResponse->getStatusCode()."\n";

if ($apiResponse->getStatusCode() === 200) {
    $data = json_decode($apiResponse->getContent(), true);
    echo 'Response has '.($data['total'] ?? 0)." total activities\n";
} elseif ($apiResponse->getStatusCode() !== 200) {
    echo 'Error response: '.substr($apiResponse->getContent(), 0, 500)."\n";
}

echo "\nDone!\n";
