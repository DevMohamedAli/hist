<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\User\Models\User;

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = User::role('employee')->first();
Auth::login($user);
$session = app('session')->driver();

$kernel = $app->make(Kernel::class);
$request = Request::create('/activitylog-ui', 'GET', [], [], [], ['HTTP_HOST' => '127.0.0.1:8000']);
$request->setLaravelSession($session);

try {
    $response = $kernel->handle($request);
    echo 'Status: '.$response->getStatusCode()."\n";
    echo "\nResponse (first 2000 chars):\n";
    echo substr($response->getContent(), 0, 2000)."\n";
} catch (Exception $e) {
    echo 'Exception: '.$e->getMessage()."\n";
    echo 'File: '.$e->getFile().':'.$e->getLine()."\n";
    echo "\nTrace:\n";
    echo $e->getTraceAsString()."\n";
}
