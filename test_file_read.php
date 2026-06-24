<?php

require 'vendor/autoload.php';

use Modules\Import\Support\SpreadsheetReader;

$reader = new SpreadsheetReader;

$testFile = 'C:/Users/darkr/Downloads/test.csv';
echo "Testing: $testFile\n";
echo 'File exists: '.(file_exists($testFile) ? 'YES' : 'NO')."\n";

if (file_exists($testFile)) {
    try {
        $data = $reader->read($testFile);
        echo 'Total rows: '.count($data)."\n";

        echo "\nChecking first 5 rows for non-empty cells:\n";
        for ($i = 0; $i < min(5, count($data)); $i++) {
            $nonEmpty = array_values(array_filter($data[$i], fn ($v) => trim((string) $v) !== ''));
            echo "Row $i: ".count($nonEmpty)." non-empty cells\n";
            if ($nonEmpty) {
                echo '  Content: '.implode(' | ', array_slice($nonEmpty, 0, 5))."\n";
            }
        }
    } catch (Exception $e) {
        echo 'Error: '.$e->getMessage()."\n";
    }
}

echo "\n--- Testing Excel file ---\n";
$excelFile = 'C:/Users/darkr/Downloads/testexcl.xlsx';
echo "Testing: $excelFile\n";
echo 'File exists: '.(file_exists($excelFile) ? 'YES' : 'NO')."\n";

if (file_exists($excelFile)) {
    try {
        $data = $reader->read($excelFile);
        echo 'Total rows: '.count($data)."\n";
        echo "First row (headers):\n";
        print_r($data[0] ?? []);
    } catch (Exception $e) {
        echo 'Error: '.$e->getMessage()."\n";
    }
}
