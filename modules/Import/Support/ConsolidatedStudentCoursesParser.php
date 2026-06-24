<?php

namespace Modules\Import\Support;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ConsolidatedStudentCoursesParser
{
    public function parse(string $filePath, bool $includeRows = true): array
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);

        $referenceSheet = $spreadsheet->getSheetByName('المقررات المرجعية');
        $detailsSheet = $spreadsheet->getSheetByName('تفاصيل المواد');

        if (! $referenceSheet || ! $detailsSheet) {
            throw new \InvalidArgumentException('يجب أن يحتوي الملف على ورقتي "المقررات المرجعية" و"تفاصيل المواد".');
        }

        $referenceCourses = $this->parseReferenceCourses($referenceSheet);
        $details = $this->parseDetails($detailsSheet, $includeRows);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return [
            'reference_courses' => $includeRows ? $referenceCourses['rows'] : [],
            'detail_rows' => $includeRows ? $details['rows'] : [],
            'summary' => [
                'reference_courses' => $referenceCourses['count'],
                'detail_rows' => $details['count'],
                'students' => count($details['students']),
                'registrations' => count($details['registrations']),
                'student_course_names' => count($details['courses']),
                'departments' => count($details['departments']),
                'semesters' => count($details['semesters']),
                'warnings' => count($details['warnings']),
                'skipped_rows' => $details['skipped_rows'],
            ],
            'warnings' => $details['warnings'],
        ];
    }

    private function parseReferenceCourses(Worksheet $sheet): array
    {
        $rows = [];
        $highestRow = $sheet->getHighestDataRow();

        for ($row = 3; $row <= $highestRow; $row++) {
            $specialization = $this->cell($sheet, $row, 1);
            $level = $this->cell($sheet, $row, 2);
            $code = $this->cell($sheet, $row, 4);
            $name = $this->cell($sheet, $row, 5);

            if ($specialization === '' || $name === '') {
                continue;
            }

            $rows[] = [
                'row' => $row,
                'specialization' => $specialization,
                'department' => $this->departmentFor($specialization),
                'semester_level' => $this->levelFor($level),
                'course_code' => $code !== '' ? $code : $this->generatedCourseCode($name, $specialization),
                'course_name' => $name,
                'units' => max(1, (int) $this->number($this->cell($sheet, $row, 6), 1)),
                'source' => $this->cell($sheet, $row, 10),
            ];
        }

        return ['rows' => $rows, 'count' => count($rows)];
    }

    private function parseDetails(Worksheet $sheet, bool $includeRows): array
    {
        $rows = [];
        $warnings = [];
        $students = [];
        $registrations = [];
        $courses = [];
        $departments = [];
        $semesters = [];
        $skippedRows = 0;
        $highestRow = $sheet->getHighestDataRow();

        for ($row = 2; $row <= $highestRow; $row++) {
            $studentName = $this->cell($sheet, $row, 2);
            $registrationNumber = $this->cell($sheet, $row, 3);
            $year = $this->cell($sheet, $row, 4);
            $season = $this->cell($sheet, $row, 5);
            $specialization = $this->cell($sheet, $row, 6);
            $level = $this->cell($sheet, $row, 7);
            $courseName = $this->cell($sheet, $row, 8);
            $workRaw = $this->cell($sheet, $row, 10);
            $finalRaw = $this->cell($sheet, $row, 11);
            $totalRaw = $this->cell($sheet, $row, 12);

            if ($studentName === '' || $courseName === '') {
                $skippedRows++;

                continue;
            }

            if ($workRaw === '' && $totalRaw === '') {
                $warnings[] = $this->warning($row, $registrationNumber, $courseName, 'grade', 'لا توجد درجة أعمال أو مجموع، تم تخطي الصف.');
                $skippedRows++;

                continue;
            }

            $semesterWork = $this->number($workRaw, 0.0);
            $total = $totalRaw === '' ? null : $this->number($totalRaw, null);
            $finalExam = $finalRaw === ''
                ? ($total === null ? 0.0 : max(0.0, $total - $semesterWork))
                : $this->number($finalRaw, 0.0);

            if ($total !== null && $total < $semesterWork) {
                $warnings[] = $this->warning($row, $registrationNumber, $courseName, 'grade', 'المجموع أقل من أعمال الفصل؛ تم استخدام النهائي 0.');
            }

            $studentKey = $registrationNumber !== '' ? $registrationNumber : $this->studentKey($studentName);
            $semesterCode = $this->semesterCode($year, $season);
            $courseCode = $this->generatedCourseCode($courseName, $specialization);

            $students[$studentKey] = true;
            if ($registrationNumber !== '') {
                $registrations[$registrationNumber] = true;
            } else {
                $warnings[] = $this->warning($row, null, $courseName, 'registration_number', 'رقم القيد فارغ؛ سيتم توليد رقم مؤقت.');
            }
            $courses[$courseName] = true;
            $departments[$specialization] = true;
            $semesters[$semesterCode] = true;

            if ($includeRows) {
                $rows[] = [
                    'row' => $row,
                    'student_name' => $studentName,
                    'registration_number' => $registrationNumber,
                    'student_key' => $studentKey,
                    'academic_year' => $year,
                    'season_ar' => $season,
                    'season' => $this->seasonFor($season),
                    'semester_code' => $semesterCode,
                    'year' => $this->yearFor($year),
                    'specialization' => $this->normalizeSpecialization($specialization),
                    'department' => $this->departmentFor($specialization),
                    'semester_level' => $this->levelFor($level),
                    'course_name' => $courseName,
                    'course_code' => $courseCode,
                    'units' => max(1, (int) $this->number($this->cell($sheet, $row, 9), 1)),
                    'semester_work' => $semesterWork,
                    'final_exam' => $finalExam,
                    'total' => $total,
                    'source_sheet' => $this->cell($sheet, $row, 13),
                    'source_file' => $this->cell($sheet, $row, 14),
                ];
            }
        }

        return [
            'rows' => $rows,
            'warnings' => $warnings,
            'count' => $highestRow - 1,
            'students' => $students,
            'registrations' => $registrations,
            'courses' => $courses,
            'departments' => $departments,
            'semesters' => $semesters,
            'skipped_rows' => $skippedRows,
        ];
    }

    private function cell(Worksheet $sheet, int $row, int $column): string
    {
        $value = $sheet->getCell([$column, $row])->getFormattedValue();

        return trim(preg_replace('/\s+/u', ' ', (string) $value) ?? '');
    }

    private function number(string $value, ?float $default): ?float
    {
        if ($value === '') {
            return $default;
        }

        $normalized = str_replace(',', '.', $value);

        return is_numeric($normalized) ? (float) $normalized : $default;
    }

    private function departmentFor(string $specialization): string
    {
        return str_contains($specialization, 'معلومات') ? 'تقنيات الحاسوب' : 'المهن الطبية';
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

    private function levelFor(string $level): int
    {
        return match (true) {
            str_contains($level, 'الثاني') => 2,
            str_contains($level, 'الثالث') => 3,
            str_contains($level, 'الرابع') => 4,
            str_contains($level, 'الخامس') => 5,
            str_contains($level, 'السادس') => 6,
            str_contains($level, 'السابع') => 7,
            str_contains($level, 'الثامن') => 8,
            is_numeric($level) => max(1, (int) $level),
            default => 1,
        };
    }

    private function seasonFor(string $season): string
    {
        return str_contains($season, 'ربيع') ? 'Spring' : 'Fall';
    }

    private function semesterCode(string $academicYear, string $season): string
    {
        return strtoupper($this->seasonFor($season)).'-'.($this->yearFor($academicYear) ?: now()->year);
    }

    private function yearFor(string $academicYear): int
    {
        if (preg_match('/20\d{2}\s*-\s*(20\d{2})/u', $academicYear, $matches)) {
            return (int) $matches[1];
        }

        if (preg_match('/20\d{2}/u', $academicYear, $matches)) {
            return (int) $matches[0];
        }

        return (int) now()->year;
    }

    private function generatedCourseCode(string $courseName, string $specialization): string
    {
        return 'CRS'.substr(strtoupper(hash('crc32b', $specialization.'|'.$courseName)), 0, 8);
    }

    private function studentKey(string $studentName): string
    {
        return '9'.substr(sprintf('%08u', crc32($studentName)), -8);
    }

    private function warning(int $row, ?string $registrationNumber, string $courseName, string $field, string $message): array
    {
        return [
            'sheet' => 'تفاصيل المواد',
            'row' => $row,
            'student_registration_number' => $registrationNumber,
            'course' => $courseName,
            'field' => $field,
            'message' => $message,
        ];
    }
}
