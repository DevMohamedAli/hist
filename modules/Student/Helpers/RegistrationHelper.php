<?php

namespace Modules\Student\Helpers;

use InvalidArgumentException;

class RegistrationHelper
{
    /**
     * Generate a 9-digit student registration number.
     *
     * Format: [AdminCode(1)][InstNum(2)][Year(2)][SemCode(1)][Seq(3)]
     *
     * @param int $administrationCode  (1 digit) - Higher Institutes Administration
     * @param int $instituteNumber     (2 digits) - Specific institute code
     * @param int $enrollmentYear      (2 digits) - e.g., 26 for 2026
     * @param int $semesterCode        (1 digit) - e.g., 1 for Spring, 2 for Fall
     * @param int $studentSequence     (3 digits) - Unique student sequence number
     * @return string                  (9-digit formatted registration number)
     * @throws InvalidArgumentException
     */
    public static function generateStudentId(
        int $administrationCode,
        int $instituteNumber,
        int $enrollmentYear,
        int $semesterCode,
        int $studentSequence
    ): string {

        // 1. Administration Code (1 digit: 0-9)
        if ($administrationCode < 0 || $administrationCode > 9) {
            throw new InvalidArgumentException("Administration code must be a single digit (0-9).");
        }
        $part1 = (string)$administrationCode;

        // 2. Institute Number (2 digits: 00-99)
        if ($instituteNumber < 0 || $instituteNumber > 99) {
            throw new InvalidArgumentException("Institute number must be between 00 and 99.");
        }
        $part2 = str_pad((string)$instituteNumber, 2, '0', STR_PAD_LEFT);

        // 3. Enrollment Year (2 digits: e.g., 26 from 2026)
        if ($enrollmentYear < 0 || $enrollmentYear > 9999) {
            throw new InvalidArgumentException("Enrollment year must be a valid year.");
        }
        // Handle 4-digit years by taking the last two digits
        $yearShort = $enrollmentYear > 99 ? $enrollmentYear % 100 : $enrollmentYear;
        $part3 = str_pad((string)$yearShort, 2, '0', STR_PAD_LEFT);

        // 4. Semester Code (1 digit: 0-9)
        if ($semesterCode < 0 || $semesterCode > 9) {
            throw new InvalidArgumentException("Semester code must be a single digit (0-9).");
        }
        $part4 = (string)$semesterCode;

        // 5. Student Sequence (3 digits: 000-999)
        if ($studentSequence < 0 || $studentSequence > 999) {
            throw new InvalidArgumentException("Student sequence number must be between 000 and 999.");
        }
        $part5 = str_pad((string)$studentSequence, 3, '0', STR_PAD_LEFT);

        return $part1 . $part2 . $part3 . $part4 . $part5;
    }
}
