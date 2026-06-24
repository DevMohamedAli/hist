<?php

use Illuminate\Support\Facades\Auth;
use Modules\User\Models\User;
use Spatie\Activitylog\Models\Activity;

require 'vendor/autoload.php';
// gete

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check activity log count
$count = Activity::count();
echo "=== Activity Log Status ===\n";
echo "Activity logs in DB: $count\n\n";

// Get a logged-in employee
$user = User::role('employee')->first()
    ?? User::role('super_admin')->first()
    ?? User::first();

if (! $user) {
    echo "ERROR: No user found in database!\n";
    exit(1);
}

echo "Test user: {$user->email} (ID: {$user->id})\n";
echo 'User roles: '.implode(', ', $user->getRoleNames()->all())."\n";
echo 'Has viewActivityLogUi permission: '.(Gate::allows('viewActivityLogUi', $user) ? 'YES' : 'NO')."\n\n";

// Set up auth
Auth::login($user);

// Try to fetch activities like the UI does
try {
    $activities = Activity::with(['causer', 'subject'])
        ->latest()
        ->paginate(25);

    echo "=== API Response Test ===\n";
    echo "Status: 200 OK\n";
    echo "Total activities: {$activities->total()}\n";
    echo "Current page count: {$activities->count()}\n";

    if ($activities->count() > 0) {
        echo "\nFirst activity:\n";
        $first = $activities->first();
        echo json_encode([
            'id' => $first->id,
            'description' => $first->description,
            'log_name' => $first->log_name,
            'causer_id' => $first->causer_id,
            'subject_type' => $first->subject_type,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n";
    }
} catch (Exception $e) {
    echo "ERROR: {$e->getMessage()}\n";
    echo $e->getTraceAsString()."\n";
}
