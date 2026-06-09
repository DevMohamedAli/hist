<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Set up auth
$user = Modules\User\Models\User::role('employee')->first();
Illuminate\Support\Facades\Auth::login($user);

// Test directly rendering the dashboard view
try {
    $view = view('activitylog-ui::pages.dashboard');
    echo "View render successful!\n";
    echo "First 1000 chars of HTML:\n";
    echo substr($view->render(), 0, 1000) . "\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo "Previous exceptions:\n";
    $prev = $e->getPrevious();
    while ($prev) {
        echo "- " . $prev->getMessage() . " (" . $prev->getFile() . ":" . $prev->getLine() . ")\n";
        $prev = $prev->getPrevious();
    }
}
