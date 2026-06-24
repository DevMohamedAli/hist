<?php

namespace Modules\Import\Support;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GradeWorkbookParser
{
    private const FORMULA_ERRORS = ['#REF!', '#VALUE!', '#DIV/0!', '#NAME?', '#N/A', '#NULL!', '#NUM!'];

    public function parse(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $context = $this->workbookContext($spreadsheet, $this->fileContext($filePath));
        $sheets = [];
        $warnings = [];
        $totals = [
            'sheets' => 0,
            'students' => 0,
            'courses' => 0,
            'grade_cells' => 0,
            'skipped_cells' => 0,
        ];

        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $parsed = $this->parseSheet($worksheet, $context);

            if ($parsed === null) {
                continue;
            }

            $sheets[] = $parsed['sheet'];
            $warnings = array_merge($warnings, $parsed['warnings']);
            $totals['sheets']++;
            $totals['students'] += count($parsed['sheet']['students']);
            $totals['courses'] += count($parsed['sheet']['courses']);
            $totals['grade_cells'] += $parsed['sheet']['grade_cells'];
            $totals['skipped_cells'] += $parsed['sheet']['skipped_cells'];
        }

        $result = [
            'sheets' => $sheets,
            'warnings' => $warnings,
            'summary' => $totals,
        ];

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $result;
    }

    private function parseSheet(Worksheet $worksheet, array $context): ?array
    {
        $highestRow = $worksheet->getHighestDataRow();
        $highestColumnIndex = Coordinate::columnIndexFromString($worksheet->getHighestDataColumn());
        $headerRow = $this->findHeaderRow($worksheet, $highestRow, $highestColumnIndex);

        if ($headerRow === null) {
            return null;
        }

        $unitsRow = $this->findUnitsRow($worksheet, $headerRow, $highestRow);
        $metadata = $this->extractMetadata($worksheet, $headerRow, $context);
        $courses = $this->extractCourses($worksheet, $headerRow, $unitsRow, $highestColumnIndex);
        $students = [];
        $warnings = $this->metadataWarnings($worksheet->getTitle(), $metadata);
        $gradeCells = 0;
        $skippedCells = 0;

        for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
            $label = $this->cellString($worksheet, $row, 4);
            $registrationNumber = $this->cellString($worksheet, $row, 3);
            $studentName = $this->cellString($worksheet, $row, 2);

            if (! str_contains($label, 'أعمال') || $registrationNumber === '' || $studentName === '') {
                continue;
            }

            $finalRow = $this->findFollowingRowByAnyLabel($worksheet, $row + 1, min($row + 2, $highestRow), ['الامتحان', 'النهائي']);
            $totalRow = $this->findFollowingRowByLabel($worksheet, $row + 1, min($row + 2, $highestRow), 'المجموع');
            $grades = [];

            foreach ($courses as $course) {
                $courseWarnings = [];
                $semesterWork = $this->numericCell($worksheet, $row, $course['column'], $courseWarnings);
                $finalExam = $finalRow ? $this->numericCell($worksheet, $finalRow, $course['column'], $courseWarnings) : null;
                $total = $totalRow ? $this->numericCell($worksheet, $totalRow, $course['column'], $courseWarnings) : null;

                foreach ($courseWarnings as $message) {
                    $warnings[] = [
                        'sheet' => $worksheet->getTitle(),
                        'row' => $row,
                        'student_registration_number' => $registrationNumber,
                        'course' => $course['name'],
                        'field' => 'grade',
                        'message' => $message,
                    ];
                    $skippedCells++;
                }

                if ($semesterWork === null && $finalExam === null && $total === null) {
                    continue;
                }

                if ($semesterWork === null && $finalExam === null && $total !== null) {
                    $semesterWork = round($total * 0.4, 2);
                    $finalExam = round($total * 0.6, 2);
                }

                $grades[] = [
                    'course_name' => $course['name'],
                    'course_code' => $course['code'],
                    'units' => $course['units'],
                    'semester_work' => $semesterWork ?? 0,
                    'final_exam' => $finalExam ?? 0,
                    'total' => $total,
                    'inferred_from_total' => $total !== null && abs((($semesterWork ?? 0) + ($finalExam ?? 0)) - $total) < 0.01,
                    'excel_column' => Coordinate::stringFromColumnIndex($course['column']),
                ];
                $gradeCells++;
            }

            $students[] = [
                'row' => $row,
                'name' => $studentName,
                'registration_number' => $registrationNumber,
                'grades' => $grades,
            ];
        }

        return [
            'sheet' => [
                'name' => $worksheet->getTitle(),
                'metadata' => $metadata,
                'courses' => $courses,
                'students' => $students,
                'grade_cells' => $gradeCells,
                'skipped_cells' => $skippedCells,
            ],
            'warnings' => $warnings,
        ];
    }

    private function findHeaderRow(Worksheet $worksheet, int $highestRow, int $highestColumnIndex): ?int
    {
        for ($row = 1; $row <= $highestRow; $row++) {
            $hasRegistration = false;
            $hasMaterials = false;

            for ($column = 1; $column <= $highestColumnIndex; $column++) {
                $value = $this->cellString($worksheet, $row, $column);
                $hasRegistration = $hasRegistration || str_contains($value, 'رقم القيد');
                $hasMaterials = $hasMaterials || str_contains($value, 'المواد');
            }

            if ($hasRegistration && $hasMaterials) {
                return $row;
            }
        }

        return null;
    }

    private function findUnitsRow(Worksheet $worksheet, int $headerRow, int $highestRow): ?int
    {
        for ($row = $headerRow + 1; $row <= min($headerRow + 3, $highestRow); $row++) {
            if (str_contains($this->cellString($worksheet, $row, 4), 'الوحدات')) {
                return $row;
            }
        }

        return null;
    }

    private function extractMetadata(Worksheet $worksheet, int $headerRow, array $context): array
    {
        $text = [];
        $metadataText = [];
        $highestColumnIndex = Coordinate::columnIndexFromString($worksheet->getHighestDataColumn());

        for ($row = 1; $row <= $headerRow; $row++) {
            for ($column = 1; $column <= $highestColumnIndex; $column++) {
                $value = $this->cellString($worksheet, $row, $column);

                if ($value !== '') {
                    $text[] = $value;

                    if ($row < $headerRow) {
                        $metadataText[] = $value;
                    }
                }
            }
        }

        $joined = implode(' ', $text);
        $metadataJoined = implode(' ', $metadataText);
        $normalized = $this->normalizeArabic($joined);
        $metadataNormalized = $this->normalizeArabic($metadataJoined);
        $sheetTitle = $this->normalizeArabic($worksheet->getTitle());
        preg_match('/20\d{2}\s*-\s*(20\d{2})/u', $joined, $yearMatches);
        $department = $this->cleanMetadataValue(
            $this->matchAfter($metadataNormalized, '/(?:القسم|قسم)\s*[:\/]\s*(.*?)(?=\s+الفصل|\s+الشعبة|\s+شعبة|\s+العام الدراسي|\s+فصل\s*[:\/]|$)/u')
                ?: $this->matchAfter($normalized, '/(?:القسم|قسم)\s*[:\/]\s*(.*?)(?=\s+الفصل|\s+الشعبة|\s+شعبة|\s+العام الدراسي|\s+فصل\s*[:\/]|$)/u')
        ) ?: $this->inferDepartment($sheetTitle, $normalized);
        $specialization = $this->cleanMetadataValue(
            $this->matchAfter($metadataNormalized, '/الشعبة\s*[:\/]\s*(.*?)(?=\s+الفصل|\s+القسم|\s+قسم|\s+العام الدراسي|\s+فصل\s*[:\/]|$)/u')
                ?: $this->matchAfter($metadataNormalized, '/شعبة\s+(.+?)(?=\s+الفصل|\s+القسم|\s+قسم|\s+العام الدراسي|\s+فصل\s*[:\/]|$)/u')
                ?: $this->matchAfter($normalized, '/الشعبة\s*[:\/]\s*(.*?)(?=\s+الفصل|\s+القسم|\s+قسم|\s+العام الدراسي|\s+فصل\s*[:\/]|$)/u')
                ?: $this->matchAfter($normalized, '/شعبة\s+(.+?)(?=\s+الفصل|\s+القسم|\s+قسم|\s+العام الدراسي|\s+فصل\s*[:\/]|$)/u')
        );
        $specialization = $specialization
            ? $this->normalizeSpecialization($specialization)
            : $this->inferSpecialization($sheetTitle, $normalized);
        $semesterLevel = $this->detectSemesterLevel($normalized.' '.$sheetTitle);
        $year = isset($yearMatches[1]) ? (int) $yearMatches[1] : ($context['year'] ?? (int) now()->year);

        return [
            'season' => $this->detectSeason($normalized, $context),
            'academic_year' => $yearMatches[0] ?? ($context['academic_year'] ?? null),
            'year' => $year,
            'department' => $department ?: 'Imported Department',
            'specialization' => $specialization ?: 'Imported Specialization',
            'semester_level' => $semesterLevel,
            'metadata_complete' => (bool) ($department && $specialization && $year && $semesterLevel > 0),
            'raw' => $joined,
        ];
    }

    private function metadataWarnings(string $sheetName, array $metadata): array
    {
        $warnings = [];

        foreach ([
            'department' => 'لم يتم التعرف على القسم، وتم استخدام قيمة افتراضية.',
            'specialization' => 'لم يتم التعرف على الشعبة، وتم استخدام قيمة افتراضية.',
        ] as $field => $message) {
            if (str_starts_with((string) $metadata[$field], 'Imported ')) {
                $warnings[] = [
                    'sheet' => $sheetName,
                    'row' => null,
                    'student_registration_number' => null,
                    'course' => null,
                    'field' => $field,
                    'message' => $message,
                ];
            }
        }

        if (($metadata['academic_year'] ?? null) === null) {
            $warnings[] = [
                'sheet' => $sheetName,
                'row' => null,
                'student_registration_number' => null,
                'course' => null,
                'field' => 'academic_year',
                'message' => 'لم يتم التعرف على العام الدراسي، وتم استخدام السنة الحالية.',
            ];
        }

        if (($metadata['semester_level'] ?? 0) < 1) {
            $warnings[] = [
                'sheet' => $sheetName,
                'row' => null,
                'student_registration_number' => null,
                'course' => null,
                'field' => 'semester_level',
                'message' => 'لم يتم التعرف على الفصل الدراسي.',
            ];
        }

        return $warnings;
    }

    private function extractCourses(Worksheet $worksheet, int $headerRow, ?int $unitsRow, int $highestColumnIndex): array
    {
        $courses = [];

        for ($column = 5; $column <= $highestColumnIndex; $column++) {
            $name = $this->cellString($worksheet, $headerRow, $column);

            if ($name === '' || $this->isSummaryHeader($name)) {
                continue;
            }

            $units = $unitsRow ? (int) $this->numericCell($worksheet, $unitsRow, $column) : 0;

            $courses[] = [
                'name' => $name,
                'code' => $this->courseCode($name),
                'units' => max($units, 1),
                'column' => $column,
            ];
        }

        return $courses;
    }

    private function findFollowingRowByLabel(Worksheet $worksheet, int $startRow, int $endRow, string $needle): ?int
    {
        for ($row = $startRow; $row <= $endRow; $row++) {
            if (str_contains($this->cellString($worksheet, $row, 4), $needle)) {
                return $row;
            }
        }

        return null;
    }

    private function findFollowingRowByAnyLabel(Worksheet $worksheet, int $startRow, int $endRow, array $needles): ?int
    {
        for ($row = $startRow; $row <= $endRow; $row++) {
            $label = $this->cellString($worksheet, $row, 4);

            foreach ($needles as $needle) {
                if (str_contains($label, $needle)) {
                    return $row;
                }
            }
        }

        return null;
    }

    private function numericCell(Worksheet $worksheet, int $row, int $column, array &$warnings = []): ?float
    {
        $value = $this->rawCellValue($worksheet, $row, $column);

        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $value = trim((string) $value);

        if (in_array($value, self::FORMULA_ERRORS, true)) {
            $warnings[] = "Unreadable formula value {$value}.";

            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function cellString(Worksheet $worksheet, int $row, int $column): string
    {
        $value = $this->rawCellValue($worksheet, $row, $column);

        if (is_float($value) && floor($value) === $value) {
            return (string) (int) $value;
        }

        return trim((string) $value);
    }

    private function rawCellValue(Worksheet $worksheet, int $row, int $column): mixed
    {
        $cell = $worksheet->getCell(Coordinate::stringFromColumnIndex($column).$row);

        try {
            return $cell->getCalculatedValue();
        } catch (\Throwable) {
            if (method_exists($cell, 'getOldCalculatedValue')) {
                $oldValue = $cell->getOldCalculatedValue();

                if ($oldValue !== null) {
                    return $oldValue;
                }
            }

            return $cell->getValue();
        }
    }

    private function isSummaryHeader(string $value): bool
    {
        foreach (['المجموع', 'مجموع الوحدات', 'المتوسط', 'النتيجة', 'التقدير', 'ملاحظات'] as $summary) {
            if (str_contains($value, $summary)) {
                return true;
            }
        }

        return false;
    }

    private function detectSeason(string $text, array $context = []): string
    {
        if (str_contains($text, 'الربيع')) {
            return 'Spring';
        }

        if (str_contains($text, 'الصيف')) {
            return 'Summer';
        }

        if (str_contains($text, 'الشتاء')) {
            return 'Winter';
        }

        return $context['season'] ?? 'Fall';
    }

    private function detectSemesterLevel(string $text): int
    {
        $levels = [
            'الأول' => 1,
            'الاول' => 1,
            'الثاني' => 2,
            'الثالث' => 3,
            'الرابع' => 4,
            'الخامس' => 5,
            'السادس' => 6,
            'السابع' => 7,
            'الثامن' => 8,
        ];

        $text = $this->normalizeArabic($text);

        foreach ($levels as $label => $level) {
            if (preg_match('/الفصل\s*[:\/]?\s*'.$label.'/u', $text)) {
                return $level;
            }
        }

        if (preg_match('/الفصل\s*[:\/]?\s*(\d+)/u', $text, $matches)) {
            return max(1, (int) $matches[1]);
        }

        if (str_contains($text, 'مشروع التخرج')) {
            return 6;
        }

        return 0;
    }

    private function matchAfter(string $text, string $pattern): ?string
    {
        if (! preg_match($pattern, $text, $matches)) {
            return null;
        }

        return trim($matches[1] ?? '') ?: null;
    }

    private function cleanMetadataValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? '');
        $value = trim($value, " \t\n\r\0\x0B:/：");
        $value = preg_replace('/\s*(الفصل|فصل)\s*[:\/]?.*$/u', '', $value) ?? $value;
        $value = preg_replace('/\s*العام الدراسي.*$/u', '', $value) ?? $value;
        $value = trim($value, " \t\n\r\0\x0B:/：");

        if (preg_match('/^عام(?:\s|$)/u', $value)) {
            return 'العام';
        }

        return trim($value) ?: null;
    }

    private function inferDepartment(string $sheetTitle, string $metadata): ?string
    {
        $text = $sheetTitle.' '.$metadata;

        if (str_contains($text, 'تقنية المعلومات') || str_contains($text, 'تقنيات الحاسب') || str_contains($text, 'تقنيات الحاسوب')) {
            return 'تقنيات الحاسوب';
        }

        foreach (['صيدلة', 'مختبرات', 'تمريض', 'قبالة', 'علاج طبيعي', 'العلاج الطبيعي', 'المهن الطبية'] as $needle) {
            if (str_contains($text, $needle)) {
                return 'المهن الطبية';
            }
        }

        return null;
    }

    private function inferSpecialization(string $sheetTitle, string $metadata): ?string
    {
        $text = $sheetTitle.' '.$metadata;

        return match (true) {
            str_contains($text, 'تقنية المعلومات') => 'تقنية المعلومات',
            str_contains($text, 'صيدلة') => 'تقنية الصيدلة',
            str_contains($text, 'مختبرات') || str_contains($text, 'المختبرات') => 'المختبرات',
            str_contains($text, 'علاج طبيعي') || str_contains($text, 'العلاج الطبيعي') => 'العلاج الطبيعي',
            str_contains($text, 'تمريض') && str_contains($text, 'قبالة') => 'التمريض والقبالة',
            str_contains($text, 'تمريض') => 'التمريض',
            str_contains($text, 'قبالة') => 'القبالة وحديثي الولادة',
            str_contains($text, 'المهن الطبية عام') => 'العام',
            default => null,
        };
    }

    private function normalizeSpecialization(string $value): string
    {
        return match (true) {
            str_contains($value, 'صيدلة') => 'تقنية الصيدلة',
            str_contains($value, 'مختبر') => 'تقنية المختبرات',
            str_contains($value, 'علاج') => 'تقنية العلاج الطبيعي',
            str_contains($value, 'تمريض') => 'تقنية التمريض العام',
            str_contains($value, 'قبالة') => 'تقنية التمريض العام',
            str_contains($value, 'معلومات') => 'تقنية المعلومات',
            str_contains($value, 'عام') => 'المهن الطبية عام',
            default => $value !== '' ? $value : 'غير محدد',
        };
    }

    private function fileContext(string $filePath): array
    {
        $name = $this->normalizeArabic(pathinfo($filePath, PATHINFO_FILENAME));
        preg_match('/(20\d{2})\s*-\s*(20\d{2})/u', $name, $rangeMatches);
        preg_match('/(?<!\d)(20\d{2})(?!\d)/u', $name, $yearMatches);
        preg_match('/(?<!\d)(\d{2})(?!\d)/u', $name, $shortYearMatches);

        return [
            'season' => str_contains($name, 'الربيع') || str_contains($name, 'ربيع') ? 'Spring' : 'Fall',
            'academic_year' => $rangeMatches[0] ?? null,
            'year' => isset($rangeMatches[2])
                ? (int) $rangeMatches[2]
                : (isset($yearMatches[1])
                    ? (int) $yearMatches[1]
                    : (isset($shortYearMatches[1]) ? 2000 + (int) $shortYearMatches[1] : null)),
        ];
    }

    private function workbookContext(Spreadsheet $spreadsheet, array $context): array
    {
        if (! empty($context['year'])) {
            return $context;
        }

        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $highestRow = min(8, $worksheet->getHighestDataRow());
            $highestColumnIndex = Coordinate::columnIndexFromString($worksheet->getHighestDataColumn());
            $text = [];

            for ($row = 1; $row <= $highestRow; $row++) {
                for ($column = 1; $column <= $highestColumnIndex; $column++) {
                    $value = $this->cellString($worksheet, $row, $column);

                    if ($value !== '') {
                        $text[] = $value;
                    }
                }
            }

            $joined = implode(' ', $text);

            if (preg_match('/20\d{2}\s*-\s*(20\d{2})/u', $joined, $rangeMatches)) {
                $context['academic_year'] = $rangeMatches[0];
                $context['year'] = (int) $rangeMatches[1];

                return $context;
            }

            if (preg_match('/(?<!\d)(20\d{2})(?!\d)/u', $joined, $yearMatches)) {
                $context['year'] = (int) $yearMatches[1];

                return $context;
            }
        }

        return $context;
    }

    private function normalizeArabic(string $value): string
    {
        $value = str_replace(['ـ', 'أ', 'إ', 'آ', 'ٱ'], ['', 'ا', 'ا', 'ا', 'ا'], $value);

        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }

    private function courseCode(string $name): string
    {
        $ascii = preg_replace('/[^A-Za-z0-9]+/', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: '');

        if ($ascii !== '') {
            return strtoupper(substr($ascii, 0, 20));
        }

        return 'CRS'.substr(strtoupper(hash('crc32b', $name)), 0, 8);
    }
}
