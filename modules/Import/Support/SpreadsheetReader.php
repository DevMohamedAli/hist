<?php

namespace Modules\Import\Support;

use RuntimeException;

class SpreadsheetReader
{
    public function read(string $filePath): array
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($extension === 'csv') {
            return $this->readCsv($filePath);
        }

        $ioFactory = '\\PhpOffice\\PhpSpreadsheet\\IOFactory';

        if (class_exists($ioFactory)) {
            $spreadsheet = $ioFactory::load($filePath);

            return $spreadsheet->getActiveSheet()->toArray();
        }

        throw new RuntimeException('Spreadsheet reader is not available for this file type.');
    }

    private function readCsv(string $filePath): array
    {
        $rows = [];
        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            throw new RuntimeException("Unable to open file: {$filePath}");
        }

        try {
            $delimiter = $this->detectCsvDelimiter($filePath);

            while (($row = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
                $rows[] = $row;
            }
        } finally {
            fclose($handle);
        }

        return $rows;
    }

    private function detectCsvDelimiter(string $filePath): string
    {
        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            throw new RuntimeException("Unable to open file: {$filePath}");
        }

        try {
            $line = fgets($handle);
        } finally {
            fclose($handle);
        }

        if ($line === false) {
            return ',';
        }

        $delimiters = [',', ';', "\t", '|'];
        $counts = [];

        foreach ($delimiters as $delimiter) {
            $counts[$delimiter] = substr_count($line, $delimiter);
        }

        arsort($counts);

        return (string) array_key_first($counts);
    }
}
