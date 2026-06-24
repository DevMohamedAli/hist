<?php

use Modules\Import\Support\GradeWorkbookParser;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function writeGradeWorkbookFixture(): string
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
        ['ت', 'الاسم', 'رقم القيد', 'المواد', 'كيمياء عامة', 'لغة إنجليزية', 'المجموع الفصلي'],
        ['', '', '', 'الوحدات', 3, 2, 5],
        [1, 'أحمد محمد', '2103241009', 'أعمال الفصل', 30, 20, 50],
        ['', '', '', 'الامتحان النهائي', 40, 30, 70],
        ['', '', '', 'المجموع', 70, 50, 120],
    ]);

    $second = $spreadsheet->createSheet();
    $second->setTitle('الفصل الثالث صيدلة');
    $second->fromArray([
        ['كشف رصد درجات'],
        [],
        ['فصل / الربيع', 'العام الدراسي / 2025-2026'],
        ['القسم / المهن الطبية', 'الفصل الثالث', 'الشعبة / الصيدلة'],
        [],
        ['ت', 'الاسم', 'رقم القيد', 'المواد', 'علم أدوية', 'المجموع الفصلي'],
        ['', '', '', 'الوحدات', 4, 4],
        [1, 'سارة علي', '2103241010', 'أعمال الفصل', 35, 35],
        ['', '', '', 'الامتحان النهائي', 55, 55],
        ['', '', '', 'المجموع', 90, 90],
    ]);

    $path = tempnam(sys_get_temp_dir(), 'grade-workbook-').'.xlsx';
    (new Xlsx($spreadsheet))->save($path);

    return $path;
}

it('parses multi sheet grade workbook metadata courses units and student grade blocks', function () {
    $path = writeGradeWorkbookFixture();

    try {
        $result = (new GradeWorkbookParser)->parse($path);
    } finally {
        @unlink($path);
    }

    expect($result['summary'])
        ->toMatchArray([
            'sheets' => 2,
            'students' => 2,
            'courses' => 3,
            'grade_cells' => 3,
        ])
        ->and($result['sheets'][0]['metadata'])
        ->toMatchArray([
            'season' => 'Fall',
            'year' => 2025,
            'department' => 'المهن الطبية',
            'specialization' => 'تقنية الصيدلة',
            'semester_level' => 2,
        ])
        ->and($result['sheets'][0]['courses'][0])
        ->toMatchArray([
            'name' => 'كيمياء عامة',
            'units' => 3,
        ])
        ->and($result['sheets'][0]['students'][0]['grades'][0])
        ->toMatchArray([
            'course_name' => 'كيمياء عامة',
            'semester_work' => 30.0,
            'final_exam' => 40.0,
            'total' => 70.0,
        ]);
});

it('parses workbook metadata when header cells are placed in far columns', function () {
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('الخامس');
    $sheet->setCellValue('J2', 'فصل / الخريف');
    $sheet->setCellValue('V2', 'العام الدراسي  / -2024-2025');
    $sheet->setCellValue('K3', 'القسم /  المهن الطبية');
    $sheet->setCellValue('O3', 'الفصل الخامس');
    $sheet->setCellValue('X3', 'الشعبة / الصيدلة');
    $sheet->fromArray([
        ['ت', 'الاسم', 'رقم القيد', 'المواد', 'صيدلة صناعية', 'المجموع الفصلي'],
        ['', '', '', 'الوحدات', 4, 4],
        [1, 'سالم علي', '2103241011', 'أعمال الفصل', 38, 38],
        ['', '', '', 'الامتحان النهائي', 52, 52],
        ['', '', '', 'المجموع', 90, 90],
    ], null, 'A6');

    $path = tempnam(sys_get_temp_dir(), 'grade-workbook-far-metadata-').'.xlsx';
    (new Xlsx($spreadsheet))->save($path);

    try {
        $result = (new GradeWorkbookParser)->parse($path);
    } finally {
        @unlink($path);
    }

    expect($result['sheets'][0]['metadata'])
        ->toMatchArray([
            'season' => 'Fall',
            'year' => 2025,
            'department' => 'المهن الطبية',
            'specialization' => 'تقنية الصيدلة',
            'semester_level' => 5,
            'metadata_complete' => true,
        ])
        ->and($result['warnings'])->toBe([]);
});
