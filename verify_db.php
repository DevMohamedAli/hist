<?php

require 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Modules\Import\Models\ImportJob;

$jobs = ImportJob::orderBy('created_at', 'desc')->limit(5)->get();
echo "Latest import jobs:\n";
foreach ($jobs as $job) {
    echo 'Job #'.$job->id.': header_row_index='.$job->header_row_index.', cols='.count($job->original_columns ?? [])."\n";
    if (count($job->original_columns ?? []) > 0) {
        echo '  First 3 columns: '.implode(', ', array_slice($job->original_columns, 0, 3))."\n";
    }
}
