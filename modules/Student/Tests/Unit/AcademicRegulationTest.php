<?php

use Illuminate\Support\Collection;
use Modules\Student\Support\AcademicRegulation;

it('uses the regulation Arabic evaluation scale', function () {
    expect(AcademicRegulation::evaluationLabel(85))->toBe('ممتاز')
        ->and(AcademicRegulation::evaluationLabel(74))->toBe('جيد')
        ->and(AcademicRegulation::evaluationLabel(49))->toBe('ضعيف')
        ->and(AcademicRegulation::evaluationLabel(34))->toBe('ضعيف جداً');
});

it('fails a course when the final exam is below half of final marks', function () {
    expect(AcademicRegulation::courseStatus(40, 29))->toBe('Failed')
        ->and(AcademicRegulation::courseStatus(20, 30))->toBe('Passed');
});

it('calculates weighted semester and cumulative averages by units', function () {
    $items = [
        ['total_mark' => 80, 'units' => 3],
        ['total_mark' => 60, 'units' => 1],
    ];

    expect(AcademicRegulation::weightedAverage($items))->toBe(75.0);
});

it('checks warnings dismissal carry and graduation project gates', function () {
    expect(AcademicRegulation::shouldWarnForSemester(34, 70))->toBeTrue()
        ->and(AcademicRegulation::shouldWarnForSemester(70, 54))->toBeTrue()
        ->and(AcademicRegulation::canCarryFailedCourses(2, 55))->toBeTrue()
        ->and(AcademicRegulation::canCarryFailedCourses(3, 70))->toBeFalse()
        ->and(AcademicRegulation::carriedUnitsWithinLimit(24))->toBeTrue()
        ->and(AcademicRegulation::carriedUnitsWithinLimit(25))->toBeFalse()
        ->and(AcademicRegulation::canRegisterGraduationProject(5, 55))->toBeTrue()
        ->and(AcademicRegulation::canRegisterGraduationProject(4, 80))->toBeFalse();

    $summaries = new Collection([
        ['semester_id' => 1, 'cumulative_gpa' => 54],
        ['semester_id' => 2, 'cumulative_gpa' => 53],
        ['semester_id' => 3, 'cumulative_gpa' => 52],
    ]);

    expect(AcademicRegulation::shouldDismissForConsecutiveLowCumulative($summaries))->toBeTrue();
});

it('centralizes registration unit limits', function () {
    expect(AcademicRegulation::maxUnitsForRegistration(70))->toBe(18)
        ->and(AcademicRegulation::maxUnitsForRegistration(75))->toBe(21)
        ->and(AcademicRegulation::maxUnitsForRegistration(80, hasWarning: true))->toBe(12)
        ->and(AcademicRegulation::maxUnitsForRegistration(54, hasCarriedCourses: true))->toBe(24);

    expect(AcademicRegulation::registrationUnitEligibility(11, 70)['eligible'])->toBeFalse()
        ->and(AcademicRegulation::registrationUnitEligibility(12, 70)['eligible'])->toBeTrue()
        ->and(AcademicRegulation::registrationUnitEligibility(22, 80)['eligible'])->toBeFalse()
        ->and(AcademicRegulation::registrationUnitEligibility(22, 80, hasCarriedCourses: true)['eligible'])->toBeTrue()
        ->and(AcademicRegulation::registrationUnitEligibility(3, 70, isGraduatingLevel: true)['eligible'])->toBeTrue();
});
