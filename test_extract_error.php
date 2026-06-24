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
$request = Request::create('/activity-logs', 'GET', [], [], [], ['HTTP_HOST' => '127.0.0.1:8000']);
$request->setLaravelSession($session);

$response = $kernel->handle($request);
file_put_contents('error_response.html', $response->getContent());
echo "Response saved to error_response.html\n";

// Also try to extract the exception from the error page
$html = $response->getContent();
// Find anything between <h1> tags
if (preg_match_all('/<h1[^>]*>(.*?)<\/h1>/i', $html, $matches)) {
    foreach ($matches[1] as $m) {
        echo 'H1: '.strip_tags($m)."\n";
    }
}
// Find the exception class/message
if (preg_match('/class="exception-message"[^>]*>(.*?)<\/span>/i', $html, $m)) {
    echo 'Exception message: '.strip_tags($m[1])."\n";
}
// Try to find any error text
if (preg_match('/<title>([^<]+)<\/title>/i', $html, $m)) {
    echo 'Title: '.$m[1]."\n";
}

$kernel->terminate($request, $response);
