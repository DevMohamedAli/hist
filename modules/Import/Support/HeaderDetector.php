<?php

namespace Modules\Import\Support;

class HeaderDetector
{
    /**
     * Detect the header row in a spreadsheet by finding the row with the most non-empty cells
     * Skips metadata rows (rows with very few cells populated)
     */
    public static function detectHeaderRow(array $rows, int $minHeaderCellsThreshold = 5): int
    {
        if (empty($rows)) {
            return 0;
        }

        $headerScores = [];

        // Score each row based on non-empty cell count
        foreach ($rows as $rowIndex => $row) {
            // Count non-empty cells
            $nonEmptyCount = count(array_filter($row, fn($v) => trim((string)$v) !== ''));
            $headerScores[$rowIndex] = $nonEmptyCount;
        }

        // Find the row with the most non-empty cells (but at least minHeaderCellsThreshold)
        arsort($headerScores);

        foreach ($headerScores as $rowIndex => $score) {
            if ($score >= $minHeaderCellsThreshold) {
                return $rowIndex;
            }
        }

        // Fallback to first row if no good header row found
        return 0;
    }

    /**
     * Extract header names from a row, using content-based names when available
     */
    public static function extractHeaders(array $row): array
    {
        $headers = [];

        foreach ($row as $index => $value) {
            $trimmedValue = trim((string)$value);
            $headers[] = $trimmedValue !== '' ? $trimmedValue : 'Column ' . ($index + 1);
        }

        return $headers;
    }

    /**
     * Get the data rows, skipping the header row and any rows before it
     */
    public static function getDataRows(array $rows, int $headerRowIndex): array
    {
        // Skip header row and any metadata rows before it
        return array_slice($rows, $headerRowIndex + 1);
    }
}
