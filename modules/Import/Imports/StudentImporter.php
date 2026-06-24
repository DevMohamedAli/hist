<?php

namespace Modules\Import\Imports;

use Modules\Student\Models\Student;

class StudentImporter extends BaseImporter
{
    public function getSchema(): array
    {
        return [
            'registration_number' => ['label' => 'رقم القيد', 'type' => 'string', 'required' => true, 'validation' => 'required|digits:9'],
            'full_name' => ['label' => 'اسم الطالب', 'type' => 'string', 'required' => true, 'validation' => 'required|string|max:150'],
            'national_id' => ['label' => 'الرقم الوطني', 'type' => 'string', 'required' => true, 'validation' => 'required|digits:12'],
            'gender' => ['label' => 'الجنس', 'type' => 'string', 'required' => true, 'validation' => 'required|in:Male,Female,ذكر,أنثى'],
            'nationality' => ['label' => 'الجنسية', 'type' => 'string', 'required' => false, 'validation' => 'nullable|string|max:50'],
            'birth_date' => ['label' => 'تاريخ الميلاد', 'type' => 'date', 'required' => true, 'validation' => 'required|date'],
            'mobile' => ['label' => 'رقم الهاتف', 'type' => 'string', 'required' => false, 'validation' => 'nullable|string|max:20'],
            'admission_date' => ['label' => 'تاريخ القبول', 'type' => 'date', 'required' => false, 'validation' => 'nullable|date'],
            'qualification' => ['label' => 'المؤهل', 'type' => 'string', 'required' => false, 'validation' => 'nullable|string|max:100'],
            'current_semester_level' => ['label' => 'الفصل الحالي', 'type' => 'integer', 'required' => false, 'validation' => 'nullable|integer|min:1'],
        ];
    }

    public function parseRow(array $row, array $mapping): array
    {
        $data = [];
        foreach ($mapping as $excelCol => $systemField) {
            $data[$systemField] = $row[$excelCol] ?? null;
        }

        return $data;
    }

    public function importRow(array $data): void
    {
        $payload = [
            'registration_number' => trim((string) ($data['registration_number'] ?? '')),
            'full_name' => trim((string) ($data['full_name'] ?? '')),
            'national_id' => trim((string) ($data['national_id'] ?? '')),
            'gender' => $this->normalizeGender($data['gender'] ?? ''),
            'nationality' => trim((string) ($data['nationality'] ?? 'ليبي')),
            'birth_date' => $data['birth_date'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'admission_date' => $data['admission_date'] ?? now()->toDateString(),
            'qualification' => $data['qualification'] ?? null,
            'current_semester_level' => (int) ($data['current_semester_level'] ?? 1),
            'status' => 'Active',
        ];

        $this->guardRequiredFields($payload);

        Student::updateOrCreate(
            ['registration_number' => $payload['registration_number']],
            $payload
        );
    }

    private function normalizeGender(mixed $value): string
    {
        $value = trim((string) $value);

        return match ($value) {
            'Male', 'male', 'M', 'm', 'ذكر' => 'Male',
            'Female', 'female', 'F', 'f', 'أنثى', 'انثى' => 'Female',
            default => $value,
        };
    }

    private function guardRequiredFields(array $payload): void
    {
        if (! preg_match('/^\d{9}$/', $payload['registration_number'])) {
            throw new \InvalidArgumentException('رقم القيد يجب أن يتكون من 9 أرقام.');
        }

        if ($payload['full_name'] === '') {
            throw new \InvalidArgumentException('اسم الطالب مطلوب.');
        }

        if (! preg_match('/^\d{12}$/', $payload['national_id'])) {
            throw new \InvalidArgumentException('الرقم الوطني يجب أن يتكون من 12 رقما.');
        }

        if (! in_array($payload['gender'], ['Male', 'Female'], true)) {
            throw new \InvalidArgumentException('الجنس يجب أن يكون ذكر أو أنثى.');
        }

        if (empty($payload['birth_date'])) {
            throw new \InvalidArgumentException('تاريخ الميلاد مطلوب.');
        }
    }
}
