<?php

namespace Modules\Student\Services;

use Illuminate\Support\Collection;
use Modules\Academic\Models\Course;
use Modules\Student\Models\Student;

class PrerequisiteService
{
    /**
     * Check if a student can enroll in a course.
     */
    public function canEnroll(Student $student, Course $course): bool
    {
        return $this->missingPrerequisites($student, $course)->isEmpty();
    }

    /**
     * Return prerequisite courses that the student has not passed yet.
     *
     * Pending, failed, carried, or same-semester registrations do not satisfy
     * a prerequisite. The prerequisite must exist as a previously passed course.
     *
     * @return Collection<int, Course>
     */
    public function missingPrerequisites(Student $student, Course $course): Collection
    {
        $course->loadMissing('prerequisites');

        if ($course->prerequisites->isEmpty()) {
            return collect();
        }

        $passedCourseIds = $student->enrollments()
            ->where('status', 'Passed')
            ->pluck('course_id')
            ->unique();

        return $course->prerequisites
            ->reject(fn (Course $prerequisite): bool => $passedCourseIds->contains($prerequisite->id))
            ->values();
    }

    /**
     * Filter a collection of courses to only eligible ones.
     */
    public function filterEligibleCourses(Student $student, $courses)
    {
        return $courses->filter(fn (Course $course): bool => $this->canEnroll($student, $course));
    }
}
