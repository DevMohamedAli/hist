<?php

namespace Modules\Student\Actions;

use Modules\Student\Models\AcademicWarning;
use Modules\Student\Models\Student;

class CheckAcademicWarningsAction
{
    /**
     * تطبيق أحكام المادة (35) للإنذارات والفصل الأكاديمي
     */
    public function execute(Student $student, int $semesterId, float $semesterGpa, float $cgpa)
    {
        // 1. فحص حالات "الإنذار الأكاديمي"
        // - إذا تحصل على تقدير عام ضعيف جداً (أقل من 35%)
        // - أو إذا قل معدله التراكمي عن 55%
        if ($semesterGpa < 35.00) {
            $this->issueWarning($student, $semesterId, 'Warning', 'الحصول على تقدير عام ضعيف جداً في الفصل الدراسي.');
        } elseif ($cgpa < 55.00) {
            $this->issueWarning($student, $semesterId, 'Warning', 'المعدل التراكمي العام أقل من 55%.');
        }

        // 2. فحص حالات "الفصل الأكاديمي (Dismissal)"
        // أ. إذا تحصل على تقدير ضعيف جداً خلال الفصلين الأول والثاني معاً
        $veryWeakWarnings = AcademicWarning::where('student_id', $student->id)
            ->where('reason', 'like', '%ضعيف جداً%')
            ->count();

        if ($veryWeakWarnings >= 2 && $student->current_semester_level <= 2) {
            $this->dismissStudent($student, $semesterId, 'الحصول على تقدير ضعيف جداً في الفصلين الأول والثاني المتتاليين.');

            return;
        }

        // ب. إذا قل معدله التراكمي عن 55% لمدة ثلاثة فصول متتالية
        $cgpaWarnings = AcademicWarning::where('student_id', $student->id)
            ->where('reason', 'like', '%المعدل التراكمي%')
            ->count();

        if ($cgpaWarnings >= 3) {
            $this->dismissStudent($student, $semesterId, 'انخفاض المعدل التراكمي عن 55% لثلاثة فصول دراسية متتالية.');

            return;
        }
    }

    private function issueWarning(Student $student, int $semesterId, string $type, string $reason)
    {
        // منع تكرار نفس الإنذار في نفس الفصل
        AcademicWarning::firstOrCreate([
            'student_id' => $student->id,
            'academic_semester_id' => $semesterId,
            'reason' => $reason,
        ], [
            'type' => $type,
        ]);
    }

    private function dismissStudent(Student $student, int $semesterId, string $reason)
    {
        // تسجيل قرار الفصل
        $this->issueWarning($student, $semesterId, 'Dismissal', $reason);

        // تغيير حالة الطالب إلى مفصول (Dismissed)
        // ملاحظة: تأكد من إضافة 'Dismissed' إلى حالات الطالب في المايجريشن الخاص به إن لم تكن موجودة
        $student->update(['status' => 'Dismissed']);
    }
}
