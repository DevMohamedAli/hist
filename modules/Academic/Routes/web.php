<?php

use Illuminate\Support\Facades\Route;
use Modules\Academic\Http\Controllers\CourseController;
use Modules\Academic\Http\Controllers\CourseClassController;
use Modules\Academic\Http\Controllers\DepartmentController;
use Modules\Academic\Http\Controllers\SemesterController;
use Modules\Academic\Http\Controllers\SpecializationController;
use Modules\Academic\Http\Controllers\StudyGroupController;

Route::middleware(['web', 'auth', 'role:employee,super_admin'])->group(function () {

    // --- 1. الأقسام الرئيسية والشعب (Form 6) ---
    Route::get('/academic/departments', [DepartmentController::class, 'index'])->name('academic.departments.index');
    Route::get('/academic/departments/create', [DepartmentController::class, 'create'])->name('academic.departments.create');
    Route::post('/academic/departments', [DepartmentController::class, 'store'])->name('academic.departments.store');
    Route::get('/academic/departments/{department}', [DepartmentController::class, 'show'])->name('academic.departments.show');
    Route::get('/academic/departments/{department}/edit', [DepartmentController::class, 'edit'])->name('academic.departments.edit');
    Route::patch('/academic/departments/{department}', [DepartmentController::class, 'update'])->name('academic.departments.update');
    Route::delete('/academic/departments/{department}', [DepartmentController::class, 'destroy'])->name('academic.departments.destroy');

    Route::get('/academic/specializations', [SpecializationController::class, 'index'])->name('academic.specializations.index');
    Route::get('/academic/specializations/create', [SpecializationController::class, 'create'])->name('academic.specializations.create');
    Route::post('/academic/specializations', [SpecializationController::class, 'store'])->name('academic.specializations.store');
    Route::get('/academic/specializations/{specialization}', [SpecializationController::class, 'show'])->name('academic.specializations.show');
    Route::get('/academic/specializations/{specialization}/edit', [SpecializationController::class, 'edit'])->name('academic.specializations.edit');
    Route::patch('/academic/specializations/{specialization}', [SpecializationController::class, 'update'])->name('academic.specializations.update');
    Route::delete('/academic/specializations/{specialization}', [SpecializationController::class, 'destroy'])->name('academic.specializations.destroy');

    // --- 2. الفصول الدراسية وتواريخ الفصول الزمنية (مادة 18) ---
    Route::get('/academic/semesters', [SemesterController::class, 'index'])->name('academic.semesters.index');
    Route::get('/academic/semesters/create', [SemesterController::class, 'create'])->name('academic.semesters.create');
    Route::post('/academic/semesters', [SemesterController::class, 'store'])->name('academic.semesters.store');
    Route::get('/academic/semesters/{semester}', [SemesterController::class, 'show'])->name('academic.semesters.show');
    Route::get('/academic/semesters/{semester}/edit', [SemesterController::class, 'edit'])->name('academic.semesters.edit');
    Route::patch('/academic/semesters/{semester}', [SemesterController::class, 'update'])->name('academic.semesters.update');
    Route::delete('/academic/semesters/{semester}', [SemesterController::class, 'destroy'])->name('academic.semesters.destroy');

    // --- 3. المقررات الدراسية (المواد ومتطلباتها السابقة) ---
    Route::get('/academic/courses', [CourseController::class, 'index'])->name('academic.courses.index');
    Route::get('/academic/courses/create', [CourseController::class, 'create'])->name('academic.courses.create');
    Route::post('/academic/courses', [CourseController::class, 'store'])->name('academic.courses.store');
    Route::get('/academic/courses/{course}', [CourseController::class, 'show'])->name('academic.courses.show');
    Route::get('/academic/courses/{course}/edit', [CourseController::class, 'edit'])->name('academic.courses.edit');
    Route::patch('/academic/courses/{course}', [CourseController::class, 'update'])->name('academic.courses.update');
    Route::delete('/academic/courses/{course}', [CourseController::class, 'destroy'])->name('academic.courses.destroy');

    Route::get('/academic/course-classes', [CourseClassController::class, 'index'])->name('academic.course-classes.index');
    Route::post('/academic/course-classes', [CourseClassController::class, 'store'])->name('academic.course-classes.store');
    Route::patch('/academic/course-classes/{courseClass}', [CourseClassController::class, 'update'])->name('academic.course-classes.update');
    Route::delete('/academic/course-classes/{courseClass}', [CourseClassController::class, 'destroy'])->name('academic.course-classes.destroy');

    Route::get('/academic/study-groups', [StudyGroupController::class, 'index'])
        ->name('academic.study-groups.index');
    Route::get('/academic/study-groups/create', [StudyGroupController::class, 'create'])
        ->name('academic.study-groups.create');
    Route::post('/academic/study-groups', [StudyGroupController::class, 'store'])
        ->name('academic.study-groups.store');
    Route::get('/academic/study-groups/{id}', [StudyGroupController::class, 'show'])
        ->name('academic.study-groups.show');
    Route::get('/academic/study-groups/{id}/edit', [StudyGroupController::class, 'edit'])
        ->name('academic.study-groups.edit');
    Route::patch('/academic/study-groups/{id}', [StudyGroupController::class, 'update'])
        ->name('academic.study-groups.update');
    Route::delete('/academic/study-groups/{id}', [StudyGroupController::class, 'destroy'])
        ->name('academic.study-groups.destroy');
});
