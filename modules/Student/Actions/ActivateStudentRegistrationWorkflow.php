<?php

namespace Modules\Student\Actions;

use Modules\Academic\Models\AcademicSemester;
use Modules\Academic\Models\StudyGroup;
use Modules\Student\Models\Student;

class ActivateStudentRegistrationWorkflow
{
    public function execute(Student $student): array
    {
        $student->loadMissing('currentStudyGroup');

        if ($student->status !== 'Active' || ! $student->current_specialization_id) {
            return [
                'activated' => false,
                'semester' => null,
                'study_group' => null,
                'message' => 'لم يتم تفعيل المسار الأكاديمي تلقائياً لأن حالة الطالب ليست نشطة أو لم يحدد له تخصص بعد.',
            ];
        }

        $semester = AcademicSemester::openForRegistration();

        if (! $semester) {
            return [
                'activated' => false,
                'semester' => null,
                'study_group' => null,
                'message' => 'تم حفظ بيانات الطالب، لكن الربط الأكاديمي التلقائي مؤجل حتى فتح فصل دراسي ضمن فترة التسجيل.',
            ];
        }

        $group = $this->resolveStudyGroup($student, $semester);

        $student->forceFill([
            'current_semester_level' => 1,
            'current_study_group_id' => $group->id,
        ])->save();

        return [
            'activated' => true,
            'semester' => $semester,
            'study_group' => $group,
            'message' => 'تم تفعيل قيد الطالب تلقائياً وإسناده إلى مجموعة المستوى الأول للفصل المفتوح حالياً.',
        ];
    }

    private function resolveStudyGroup(Student $student, AcademicSemester $semester): StudyGroup
    {
        $currentGroup = $student->currentStudyGroup;

        if ($currentGroup
            && (int) $currentGroup->specialization_id === (int) $student->current_specialization_id
            && (int) $currentGroup->academic_semester_id === (int) $semester->id
            && (int) $currentGroup->semester_level === 1) {
            return $currentGroup;
        }

        $group = StudyGroup::query()
            ->where('specialization_id', $student->current_specialization_id)
            ->where('academic_semester_id', $semester->id)
            ->where('semester_level', 1)
            ->withCount('students')
            ->orderBy('students_count')
            ->orderBy('id')
            ->get()
            ->first(fn (StudyGroup $group): bool => ! $group->capacity || $group->students_count < $group->capacity);

        if ($group) {
            return $group;
        }

        return StudyGroup::create([
            'specialization_id' => $student->current_specialization_id,
            'academic_semester_id' => $semester->id,
            'semester_level' => 1,
            'group_name' => $this->nextGroupName($student, $semester),
            'capacity' => 30,
        ]);
    }

    private function nextGroupName(Student $student, AcademicSemester $semester): string
    {
        $count = StudyGroup::query()
            ->where('specialization_id', $student->current_specialization_id)
            ->where('academic_semester_id', $semester->id)
            ->where('semester_level', 1)
            ->count();

        return chr(65 + min($count, 25));
    }
}
