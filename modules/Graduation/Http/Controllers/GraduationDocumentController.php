<?php

namespace Modules\Graduation\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Modules\Academic\Models\AcademicSemester;
use Modules\Graduation\Actions\GraduationEligibilityAction;
use Modules\Graduation\Models\GraduationRecord;
use Modules\Graduation\Support\ArabicPdfText;
use Modules\Shared\Http\Controllers\Controller;
use Modules\Student\Models\CourseEnrollment;
use Modules\Student\Support\AcademicRegulation;

class GraduationDocumentController extends Controller
{
    public function certificate(GraduationRecord $record)
    {
        $record->load(['student.currentSpecialization.department', 'specialization.department', 'approver']);

        return Pdf::loadView('graduation::certificate', [
            'record' => $record,
            'evaluation' => AcademicRegulation::evaluationLabel((float) $record->cgpa),
            'printed_at' => now(),
            'pdfText' => new ArabicPdfText,
        ])
            ->setPaper('a4', 'portrait')
            ->setOption('defaultFont', 'Cairo')
            ->setOption('isFontSubsettingEnabled', true)
            ->download('certificate-'.$record->certificate_number.'.pdf');
    }

    public function studyReport(GraduationRecord $record, GraduationEligibilityAction $eligibilityAction)
    {
        $record->load(['student.currentSpecialization.department', 'specialization.department', 'approver']);
        $student = $record->student;
        $enrollments = $student->enrollments()
            ->with(['course', 'studyGroup.academicSemester', 'class.semester'])
            ->get()
            ->values();

        $studyPlanSections = $this->buildStudyPlanSections($enrollments);
        $semesterCount = collect($studyPlanSections)
            ->flatMap(fn (array $level) => $level['semesters'])
            ->count();

        return Pdf::loadView('graduation::study-report', [
            'record' => $record,
            'student' => $student,
            'eligibility' => $eligibilityAction->execute($student),
            'studyPlanSections' => $studyPlanSections,
            'reportSummary' => [
                'levels_count' => count($studyPlanSections),
                'semesters_count' => $semesterCount,
                'courses_count' => $enrollments->count(),
                'completed_units' => (int) $record->total_units,
            ],
            'printed_at' => now(),
            'pdfText' => new ArabicPdfText,
        ])
            ->setPaper('a4', 'portrait')
            ->setOption('defaultFont', 'Cairo')
            ->setOption('isFontSubsettingEnabled', true)
            ->download('study-report-'.$record->certificate_number.'.pdf');
    }

    /**
     * @return array<int, array{level:int,label:string,courses_count:int,total_units:int,semesters: array<int, array{label:string,code:string,season:?string,year:?int,courses: array<int, array<string, mixed>>}>}>
     */
    private function buildStudyPlanSections(Collection $enrollments): array
    {
        $sortedEnrollments = $enrollments->sortBy([
            fn (CourseEnrollment $left, CourseEnrollment $right) => $this->semesterLevel($left) <=> $this->semesterLevel($right),
            fn (CourseEnrollment $left, CourseEnrollment $right) => strcmp($this->semesterSortKey($left), $this->semesterSortKey($right)),
            fn (CourseEnrollment $left, CourseEnrollment $right) => strcmp((string) $left->course?->name, (string) $right->course?->name),
        ])->values();

        return $sortedEnrollments
            ->groupBy(fn (CourseEnrollment $enrollment) => $this->semesterLevel($enrollment))
            ->map(function (Collection $levelEnrollments, int|string $level): array {
                $semesterGroups = $levelEnrollments->groupBy(fn (CourseEnrollment $enrollment) => $this->semesterGroupingKey($enrollment));

                return [
                    'level' => (int) $level,
                    'label' => 'المستوى '.$level,
                    'courses_count' => $levelEnrollments->count(),
                    'total_units' => (int) $levelEnrollments->sum(fn (CourseEnrollment $enrollment) => (int) ($enrollment->course?->units ?? 0)),
                    'semesters' => $semesterGroups
                        ->map(function (Collection $semesterEnrollments): array {
                            $firstEnrollment = $semesterEnrollments->first();
                            $semester = $firstEnrollment?->studyGroup?->academicSemester ?? $firstEnrollment?->class?->semester;

                            return [
                                'label' => $this->semesterDisplayLabel($semester),
                                'code' => $semester?->code ?? 'غير محدد',
                                'season' => $semester?->season,
                                'year' => $semester?->year,
                                'courses' => $semesterEnrollments
                                    ->sortBy([
                                        fn (CourseEnrollment $left, CourseEnrollment $right) => strcmp((string) $left->course?->code, (string) $right->course?->code),
                                        fn (CourseEnrollment $left, CourseEnrollment $right) => strcmp((string) $left->course?->name, (string) $right->course?->name),
                                    ])
                                    ->values()
                                    ->map(fn (CourseEnrollment $enrollment): array => [
                                        'course_code' => $enrollment->course?->code ?? '-',
                                        'course_name' => $enrollment->course?->name ?? '-',
                                        'units' => (int) ($enrollment->course?->units ?? 0),
                                        'semester_work' => $enrollment->raw_semester_work,
                                        'final_exam' => $enrollment->raw_final_exam,
                                        'total_mark' => $enrollment->total_mark,
                                        'grade_evaluation' => $enrollment->grade_evaluation,
                                        'status' => $enrollment->status,
                                        'is_carried' => (bool) $enrollment->is_carried,
                                    ])
                                    ->all(),
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function semesterLevel(CourseEnrollment $enrollment): int
    {
        return (int) ($enrollment->studyGroup?->semester_level ?? 0);
    }

    private function semesterGroupingKey(CourseEnrollment $enrollment): string
    {
        $semester = $enrollment->studyGroup?->academicSemester ?? $enrollment->class?->semester;

        return $semester ? 'semester-'.$semester->id : 'enrollment-'.$enrollment->id;
    }

    private function semesterSortKey(CourseEnrollment $enrollment): string
    {
        $semester = $enrollment->studyGroup?->academicSemester ?? $enrollment->class?->semester;
        $seasonOrder = $semester?->season === 'Spring' ? '1' : '2';

        return sprintf(
            '%04d-%s-%s',
            (int) ($semester?->year ?? 0),
            $seasonOrder,
            (string) ($semester?->code ?? 'ZZZ')
        );
    }

    private function semesterDisplayLabel(?AcademicSemester $semester): string
    {
        if (! $semester) {
            return 'غير محدد';
        }

        $season = $semester->season === 'Spring' ? 'الربيع' : 'الخريف';

        return trim($season.' '.$semester->year);
    }
}
