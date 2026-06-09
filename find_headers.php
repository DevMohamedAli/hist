<?php
require 'vendor/autoload.php';

use Modules\Import\Support\SpreadsheetReader;

$reader = new SpreadsheetReader();
$data = $reader->read('C:/Users/darkr/Downloads/test.csv');

echo "Finding the header row with most non-empty cells:\n";
$headerScores = [];
for ($i = 0; $i < min(20, count($data)); $i++) {
    $nonEmptyCount = count(array_filter($data[$i], fn($v) => trim((string)$v) !== ''));
    $headerScores[$i] = $nonEmptyCount;
    echo "Row $i: $nonEmptyCount non-empty cells\n";
    if ($nonEmptyCount > 5) {
        echo "  --> " . implode(' | ', array_slice(array_filter($data[$i], fn($v) => trim((string)$v) !== ''), 0, 10)) . "\n";
    }
}

arsort($headerScores);
$headerRowIndex = array_key_first($headerScores);
echo "\nBest header row appears to be row $headerRowIndex with {$headerScores[$headerRowIndex]} non-empty cells\n";
echo "Row $headerRowIndex content:\n";
print_r($data[$headerRowIndex]);
echo "\nNext data row:\n";
print_r($data[$headerRowIndex + 1]);
