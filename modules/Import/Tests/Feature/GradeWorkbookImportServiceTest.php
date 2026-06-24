<?php

use Illuminate\Http\UploadedFile;
use Modules\Academic\Models\Course;
use Modules\Academic\Models\CourseClass;
use Modules\Academic\Models\Department;
use Modules\Academic\Models\Specialization;
use Modules\Academic\Models\StudyGroup;
use Modules\Import\Models\ImportJob;
use Modules\Import\Services\GradeWorkbookImportService;
use Modules\Staff\Models\Instructor;
use Modules\Student\Models\CourseEnrollment;
use Modules\Student\Models\Student;
use Modules\User\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function writeImportServiceGradeWorkbookFixture(): string
{
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('الفصل الثاني صيدلة');
    $sheet->fromArray([
        ['كشف رصد درجات'],
        [],
        ['فصل / الخريف', 'العام الدراسي / 2024-2025'],
        ['القسم / المهن الطبية', 'الفصل الثاني', 'الشعبة / الصيدلة'],
        [],
        ['ت', 'الاسم', 'رقم القيد', 'المواد', 'كيمياء عامة', 'المجموع الفصلي'],
        ['', '', '', 'الوحدات', 3, 3],
        [1, 'أحمد محمد', '2103241009', 'أعمال الفصل', 30, 30],
        ['', '', '', 'الامتحان النهائي', 40, 40],
        ['', '', '', 'المجموع', 70, 70],
    ]);

    $path = tempnam(sys_get_temp_dir(), 'grade-import-service-').'.xlsx';
    (new Xlsx($spreadsheet))->save($path);

    return $path;
}

it('creates minimal academic records and records workbook grades', function () {
    $source = writeImportServiceGradeWorkbookFixture();

    try {
        $uploaded = new UploadedFile($source, 'grades.xlsx', test: true);
        $storedPath = $uploaded->store('imports');
    } finally {
        @unlink($source);
    }

    $job = ImportJob::create([
        'user_id' => User::factory()->create()->id,
        'type' => 'grade_workbook',
        'file_name' => 'grades.xlsx',
        'file_path' => $storedPath,
        'status' => 'pending',
    ]);

    $result = app(GradeWorkbookImportService::class)->import($job);

    expect($result['summary']['imported_grades'])->toBe(1)
        ->and(Department::where('name', 'المهن الطبية')->exists())->toBeTrue()
        ->and(Specialization::where('name', 'تقنية الصيدلة')->exists())->toBeTrue()
        ->and(Student::where('registration_number', '2103241009')->exists())->toBeTrue()
        ->and(Course::where('name', 'كيمياء عامة')->exists())->toBeTrue()
        ->and(StudyGroup::count())->toBe(1)
        ->and(CourseClass::count())->toBe(1)
        ->and(Instructor::where('email', 'imported-grades-placeholder@example.local')->exists())->toBeTrue();

    $enrollment = CourseEnrollment::firstOrFail();

    expect((float) $enrollment->raw_semester_work)->toBe(30.0)
        ->and((float) $enrollment->raw_final_exam)->toBe(40.0)
        ->and((float) $enrollment->total_mark)->toBe(70.0)
        ->and($enrollment->status)->toBe('Passed');
});

it('uses an alternate specialization code when the generated code already exists', function () {
    $source = writeImportServiceGradeWorkbookFixture();

    try {
        $uploaded = new UploadedFile($source, 'grades.xlsx', test: true);
        $storedPath = $uploaded->store('imports');
    } finally {
        @unlink($source);
    }

    $existingDepartment = Department::create([
        'name' => 'قسم آخر',
        'code' => 'DEP-OTHER',
    ]);
    Specialization::create([
        'department_id' => $existingDepartment->id,
        'name' => 'تخصص آخر',
        'code' => 'SPC'.substr(strtoupper(hash('crc32b', 'تقنية الصيدلة')), 0, 8),
        'semesters_count' => 6,
    ]);

    $job = ImportJob::create([
        'user_id' => User::factory()->create()->id,
        'type' => 'grade_workbook',
        'file_name' => 'grades.xlsx',
        'file_path' => $storedPath,
        'status' => 'pending',
    ]);

    app(GradeWorkbookImportService::class)->import($job);

    $importedSpecialization = Specialization::query()
        ->where('name', 'تقنية الصيدلة')
        ->firstOrFail();

    expect($importedSpecialization->code)
        ->not->toBe('SPC'.substr(strtoupper(hash('crc32b', 'تقنية الصيدلة')), 0, 8))
        ->and(CourseEnrollment::count())->toBe(1);
});
