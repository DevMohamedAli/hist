<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Modules\Academic\Models\Course;
use Modules\Academic\Models\Department;
use Modules\Academic\Models\Specialization;
use Modules\Import\Services\ConsolidatedStudentCoursesImportService;
use Modules\Student\Models\CourseEnrollment;
use Modules\Student\Models\Student;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function writeConsolidatedStudentCoursesFixture(): string
{
    $spreadsheet = new Spreadsheet;
    $reference = $spreadsheet->getActiveSheet();
    $reference->setTitle('المقررات المرجعية');
    $reference->fromArray([
        ['المقررات الدراسية المرجعية لكل تخصص'],
        ['التخصص', 'الفصل/المستوى', 'ت', 'رمز المقرر', 'اسم المقرر', 'الوحدات', 'نظري', 'عملي', 'تمارين/مجموع', 'المصدر', 'صفحة المصدر'],
        ['تقنية الصيدلة', 'الأول', 1, 'PH101', 'كيمياء عامة', 3, '', '', '', 'fixture.pdf', 1],
        ['تقنية الصيدلة', 'الثاني', 2, 'PH102', 'صيدلانيات', 4, '', '', '', 'fixture.pdf', 1],
    ]);

    $details = $spreadsheet->createSheet();
    $details->setTitle('تفاصيل المواد');
    $details->fromArray([
        ['ت', 'اسم الطالب', 'رقم القيد', 'العام الدراسي', 'الفصل الدراسي', 'القسم/الشعبة', 'الفصل/المستوى', 'المادة', 'الوحدات', 'أعمال الفصل', 'النهائي', 'المجموع', 'ورقة المصدر', 'ملف المصدر'],
        [1, 'أحمد محمد', '2103000001', '2024-2025', 'الخريف', 'صيدلة', 'الأول', 'كيمياء عامة', 3, 30, '', 70, 'ورقة', 'fixture.xlsx'],
        [2, 'أحمد محمد', '2103000001', '2024-2025', 'الربيع', 'صيدلة', 'الثاني', 'صيدلانيات', 4, 35, 40, 75, 'ورقة', 'fixture.xlsx'],
        [3, 'سارة علي', '', '2024-2025', 'الخريف', 'صيدلة', 'الأول', 'كيمياء عامة', 3, '', '', '', 'ورقة', 'fixture.xlsx'],
    ]);

    $path = tempnam(sys_get_temp_dir(), 'consolidated-import-').'.xlsx';
    (new Xlsx($spreadsheet))->save($path);

    return $path;
}

it('parses consolidated workbook sheets and derives final exam from total', function () {
    $path = writeConsolidatedStudentCoursesFixture();

    try {
        $result = app(Modules\Import\Support\ConsolidatedStudentCoursesParser::class)->parse($path);
    } finally {
        @unlink($path);
    }

    expect($result['summary']['reference_courses'])->toBe(2)
        ->and($result['summary']['detail_rows'])->toBe(3)
        ->and($result['summary']['skipped_rows'])->toBe(1)
        ->and($result['detail_rows'][0]['final_exam'])->toBe(40.0)
        ->and($result['warnings'])->not->toBeEmpty();
});

it('imports consolidated workbook records and remains idempotent', function () {
    $path = writeConsolidatedStudentCoursesFixture();

    try {
        $service = app(ConsolidatedStudentCoursesImportService::class);
        $first = $service->import($path);
        $second = $service->import($path);
    } finally {
        @unlink($path);
    }

    expect($first['summary']['grades_recorded'])->toBe(2)
        ->and($second['summary']['grades_recorded'])->toBe(2)
        ->and(Department::where('name', 'المهن الطبية')->exists())->toBeTrue()
        ->and(Specialization::where('name', 'تقنية الصيدلة')->exists())->toBeTrue()
        ->and(Course::where('code', 'PH101')->exists())->toBeTrue()
        ->and(Student::where('registration_number', '2103000001')->exists())->toBeTrue()
        ->and(CourseEnrollment::count())->toBe(2);

    $enrollment = CourseEnrollment::whereHas('course', fn ($query) => $query->where('name', 'كيمياء عامة'))->firstOrFail();

    expect((float) $enrollment->raw_semester_work)->toBe(30.0)
        ->and((float) $enrollment->raw_final_exam)->toBe(40.0)
        ->and((float) $enrollment->total_mark)->toBe(70.0)
        ->and($enrollment->status)->toBe('Passed');
});

it('writes a dry-run report from the artisan command', function () {
    $path = writeConsolidatedStudentCoursesFixture();

    try {
        Artisan::call('import:consolidated-student-courses', [
            'file' => $path,
            '--dry-run' => true,
            '--report' => 'consolidated-test-report.json',
        ]);
    } finally {
        @unlink($path);
    }

    expect(Storage::exists('consolidated-test-report.json'))->toBeTrue()
        ->and(json_decode(Storage::get('consolidated-test-report.json'), true)['mode'])->toBe('dry-run');
});
