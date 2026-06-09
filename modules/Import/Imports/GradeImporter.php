<?php

namespace Modules\Import\Imports;

use Modules\Academic\Models\Course;
use Modules\Student\Actions\RecordCourseGrades;
use Modules\Student\Models\CourseEnrollment;
use Modules\Student\Models\Student;

class GradeImporter extends BaseImporter
{
    public function getSchema(): array
    {
        return [
            'student_reg' => ['label' => 'رقم القيد', 'type' => 'string', 'required' => true, 'validation' => 'required|digits:9'],
            'course_code' => ['label' => 'رمز المقرر', 'type' => 'string', 'required' => true, 'validation' => 'required|string|max:20'],
            'semester_work' => ['label' => 'أعمال الفصل', 'type' => 'number', 'required' => true, 'validation' => 'required|numeric|min:0|max:40'],
            'final_exam' => ['label' => 'الامتحان النهائي', 'type' => 'number', 'required' => true, 'validation' => 'required|numeric|min:0|max:60'],
        ];
    }

    public function parseRow(array $row, array $mapping): array
    {
        $data = [];

        foreach ($mapping as $excelCol => $systemField) {
            if ($systemField === null || $systemField === '') {
                continue;
            }

            $data[$systemField] = $row[$excelCol] ?? null;
        }

        if (isset($data['course_code'])) {
            $data['course_code'] = strtoupper(trim((string) $data['course_code']));
        }

        if (isset($data['student_reg'])) {
            $data['student_reg'] = trim((string) $data['student_reg']);
        }

        return $data;
    }

    public function importRow(array $data): void
    {
        $student = Student::query()
            ->where('registration_number', $data['student_reg'] ?? null)
            ->first();

        if (! $student) {
            throw new \InvalidArgumentException('لم يتم العثور على الطالب برقم القيد: ' . ($data['student_reg'] ?? '-'));
        }

        $course = Course::query()
            ->where('code', strtoupper(trim((string) ($data['course_code'] ?? ''))))
            ->first();

        if (! $course) {
            throw new \InvalidArgumentException('لم يتم العثور على المقرر بالرمز: ' . ($data['course_code'] ?? '-'));
        }

        $enrollment = CourseEnrollment::query()
            ->where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->latest()
            ->first();

        if (! $enrollment) {
            throw new \InvalidArgumentException("الطالب {$student->registration_number} غير مسجل في المقرر {$course->code}.");
        }

        app(RecordCourseGrades::class)->execute(
            $enrollment->id,
            (float) $data['semester_work'],
            (float) $data['final_exam'],
        );
    }
}
