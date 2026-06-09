<?php

namespace Modules\Import\Imports;

use Illuminate\Support\Facades\Log;
use Modules\Academic\Models\Course;
use Smalot\PdfParser\Parser;
use Throwable;

class PdfCoursesImport
{
    public function importFromPdf(string $filePath): array
    {
        $rows = $this->extractRows($filePath);

        $summary = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
            'rows' => [],
        ];

        foreach ($rows as $row) {
            if (empty($row['code']) || empty($row['name']) || empty($row['units'])) {
                $summary['skipped']++;
                $summary['errors'][] = 'تم تجاوز صف غير مكتمل: ' . ($row['raw'] ?? '');
                continue;
            }

            try {
                $course = Course::updateOrCreate(
                    ['code' => $row['code']],
                    [
                        'name' => mb_substr($row['name'], 0, 100),
                        'units' => (int) $row['units'],
                        'has_practical' => $this->hasPractical($row),
                    ],
                );

                $course->wasRecentlyCreated ? $summary['created']++ : $summary['updated']++;
                $summary['rows'][] = $row;
            } catch (Throwable $exception) {
                $summary['skipped']++;
                $summary['errors'][] = "تعذر حفظ المقرر {$row['code']}: {$exception->getMessage()}";

                Log::warning('PDF course import row failed', [
                    'row' => $row,
                    'exception' => $exception,
                ]);
            }
        }

        return $summary;
    }

    public function previewFromPdf(string $filePath, int $limit = 30): array
    {
        return array_slice($this->extractRows($filePath), 0, $limit);
    }

    public function extractRows(string $filePath): array
    {
        try {
            $text = (new Parser())->parseFile($filePath)->getText();
        } catch (Throwable $exception) {
            Log::error('PDF course import parsing failed', [
                'file' => $filePath,
                'exception' => $exception,
            ]);

            throw new \RuntimeException('تعذر قراءة ملف PDF. تأكد أن الملف يحتوي على نص قابل للاستخراج وليس صورا فقط.');
        }

        return $this->parseText($text);
    }

    private function parseText(string $text): array
    {
        $lines = preg_split('/\R/u', $this->normalizeText($text)) ?: [];
        $rows = [];
        $pending = null;

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || $this->isNoiseLine($line)) {
                continue;
            }

            $segments = $this->splitLineIntoCourseSegments($line);

            if ($segments !== []) {
                foreach ($segments as $segment) {
                    $parsed = $this->parseLine($segment);

                    if (! $parsed) {
                        continue;
                    }

                    if ($pending) {
                        $rows[] = $pending;
                    }

                    $pending = $parsed;
                }

                continue;
            }

            if ($pending && ! $this->looksLikeHeader($line)) {
                $pending['name'] = $this->cleanCourseName($pending['name'] . ' ' . $line);
                $pending['raw'] = trim($pending['raw'] . ' ' . $line);
            }
        }

        if ($pending) {
            $rows[] = $pending;
        }

        return array_values(array_filter($rows, fn (array $row): bool => $row['code'] !== '' && $row['name'] !== ''));
    }

    private function splitLineIntoCourseSegments(string $line): array
    {
        if (! preg_match_all($this->courseCodePattern(), $line, $matches, PREG_OFFSET_CAPTURE)) {
            return [];
        }

        $codes = $matches['code'] ?? [];

        if (count($codes) === 1) {
            return [$line];
        }

        $segments = [];
        $lineLength = strlen($line);

        foreach ($codes as $index => $codeMatch) {
            [$code, $codeStart] = $codeMatch;
            $codeEnd = $codeStart + strlen($code);
            $nextStart = isset($codes[$index + 1]) ? $codes[$index + 1][1] : $lineLength;
            $content = substr($line, $codeEnd, max(0, $nextStart - $codeEnd));

            $segments[] = trim($code . ' ' . $this->removeTrailingTableIndex($content));
        }

        return array_values(array_filter($segments, fn (string $segment): bool => $segment !== ''));
    }

    private function parseLine(string $line): ?array
    {
        if (! preg_match($this->courseCodePattern(), $line, $match, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $code = $this->normalizeCode($match['code'][0]);
        $before = trim(substr($line, 0, $match['code'][1]));
        $after = trim(substr($line, $match['code'][1] + strlen($match['code'][0])));
        $rest = trim($before . ' ' . $after);

        $parts = preg_split('/\s{2,}|\t|\|/u', $rest) ?: [];
        $parts = array_values(array_filter(array_map('trim', $parts), fn (string $value): bool => $value !== ''));

        $units = $this->extractUnits($parts, $rest);
        $name = $this->extractName($parts, $rest, $units);

        return [
            'code' => $code,
            'name' => $this->isValidCourseName($name) ? $name : '',
            'units' => $units,
            'semester' => $this->extractSemester($line),
            'weekly_hours' => $this->extractWeeklyHours($line),
            'raw' => $line,
        ];
    }

    private function extractName(array $parts, string $rest, ?int $units): string
    {
        $nameParts = array_values(array_filter($parts, function (string $part) use ($units): bool {
            $normalized = $this->normalizeDigits($part);

            if ($units !== null && $normalized === (string) $units) {
                return false;
            }

            return ! preg_match('/^(semester|sem|الفصل|الساعات|hours?|units?|الوحدات)$/iu', $part);
        }));

        return $this->cleanCourseName($nameParts[0] ?? $rest);
    }

    private function extractUnits(array $parts, string $line): ?int
    {
        foreach (array_reverse($parts) as $part) {
            $normalized = $this->normalizeDigits($part);

            if (preg_match('/^\d$/', $normalized) && (int) $normalized >= 1 && (int) $normalized <= 9) {
                return (int) $normalized;
            }
        }

        if (preg_match('/(?:units?|وحدات|الوحدات)\s*[:\-]?\s*(\d)/iu', $this->normalizeDigits($line), $match)) {
            return (int) $match[1];
        }

        return null;
    }

    private function extractSemester(string $line): ?int
    {
        $line = $this->normalizeDigits($line);

        if (preg_match('/(?:semester|sem|الفصل)\s*[:\-]?\s*(\d+)/iu', $line, $match)) {
            return (int) $match[1];
        }

        return null;
    }

    private function extractWeeklyHours(string $line): ?string
    {
        $line = $this->normalizeDigits($line);

        if (preg_match('/(?:hours?|الساعات)\s*[:\-]?\s*([0-9+\s\/]+)/iu', $line, $match)) {
            return trim($match[1]);
        }

        return null;
    }

    private function hasPractical(array $row): bool
    {
        $haystack = mb_strtolower(($row['weekly_hours'] ?? '') . ' ' . ($row['raw'] ?? ''));

        return str_contains($haystack, 'practical')
            || str_contains($haystack, 'lab')
            || str_contains($haystack, 'عملي')
            || str_contains($haystack, 'معمل');
    }

    private function normalizeText(string $text): string
    {
        $text = str_replace(["\u{200E}", "\u{200F}", "\u{202A}", "\u{202B}", "\u{202C}"], '', $text);
        $text = $this->normalizeDigits($text);
        $text = preg_replace('/[ ]{3,}/u', '  ', $text) ?? $text;

        return trim($text);
    }

    private function normalizeDigits(string $value): string
    {
        return strtr($value, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]);
    }

    private function normalizeCode(string $code): string
    {
        return strtoupper(preg_replace('/\s+/u', '', trim($code)) ?? trim($code));
    }

    private function cleanCourseName(string $name): string
    {
        $name = preg_replace($this->courseCodePattern(), '', $name) ?? $name;
        $name = preg_replace('/\b(units?|hours?|semester|sem)\b\s*[:\-]?\s*\d*/iu', '', $name) ?? $name;
        $name = preg_replace('/(?:الوحدات|الساعات|الفصل)\s*[:\-]?\s*\d*/u', '', $name) ?? $name;
        $name = preg_replace('/^\s*\d{1,3}\s+|\s+\d{1,3}\s*$/u', '', $name) ?? $name;
        $name = preg_replace('/\s+/u', ' ', $name) ?? $name;
        $name = trim($name, " \t\n\r\0\x0B-:|");

        return $this->fixPdfRtlName($name);
    }

    private function removeTrailingTableIndex(string $content): string
    {
        return preg_replace('/\s+\d{1,3}\s*$/u', '', $content) ?? $content;
    }

    private function fixPdfRtlName(string $name): string
    {
        if (! $this->shouldReverseArabicName($name)) {
            return $name;
        }

        $reversed = $this->reverseUtf8($name);
        $reversed = preg_replace('/\s+/u', ' ', $reversed) ?? $reversed;

        // Common artifact: roman course suffixes appear before the Arabic name after reversal.
        $reversed = preg_replace('/^([IVX]+)\s+(.+)$/u', '$2 $1', trim($reversed)) ?? $reversed;

        return trim($reversed);
    }

    private function shouldReverseArabicName(string $name): bool
    {
        if (! preg_match('/\p{Arabic}/u', $name)) {
            return false;
        }

        if (preg_match('/لغة|علم|تمريض|كيمياء|فيزياء|رعاية|دراسات|حاسب|أساسيات|أحياء|تشريح|وظائف/u', $name)) {
            return false;
        }

        return preg_match('/ةغل|ملع|ضيرمت|ءايميك|ءايزيف|ةياعر|تاسارد|بساح|تايساسأ|ءايحأ|حيرشت|فئاظو/u', $name) === 1;
    }

    private function isValidCourseName(string $name): bool
    {
        $name = trim($name);

        if ($name === '' || preg_match('/^\d+$/u', $name)) {
            return false;
        }

        return preg_match('/[\p{Arabic}A-Za-z]/u', $name) === 1;
    }

    private function reverseUtf8(string $value): string
    {
        return implode('', array_reverse(preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY) ?: []));
    }

    private function courseCodePattern(): string
    {
        return '/(?<code>[A-Z]{2,6}\s*[-\/]?\s*\d{2,4}[A-Z]?)/u';
    }

    private function isNoiseLine(string $line): bool
    {
        return $this->looksLikeHeader($line)
            || preg_match('/^(page|صفحة)\s*\d+/iu', $line)
            || mb_strlen($line) < 4;
    }

    private function looksLikeHeader(string $line): bool
    {
        return preg_match('/course\s*code|course\s*name|units|اسم\s*المقرر|رمز\s*المقرر|الوحدات/iu', $line) === 1;
    }
}
