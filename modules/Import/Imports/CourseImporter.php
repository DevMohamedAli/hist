<?php

namespace Modules\Import\Imports;

use Modules\Academic\Models\Course;

class CourseImporter extends BaseImporter
{
    public function getSchema(): array
    {
        return [
            'code' => ['label' => 'رمز المقرر', 'type' => 'string', 'required' => true, 'validation' => 'required|max:20'],
            'name' => ['label' => 'اسم المقرر', 'type' => 'string', 'required' => true, 'validation' => 'required|max:100'],
            'units' => ['label' => 'الوحدات', 'type' => 'integer', 'required' => true, 'validation' => 'required|integer|min:1|max:9'],
            'has_practical' => ['label' => 'به عملي', 'type' => 'boolean', 'required' => false, 'validation' => 'nullable'],
        ];
    }

    public function parseRow(array $row, array $mapping): array
    {
        $data = [];
        foreach ($mapping as $excelCol => $systemField) {
            // $excelCol is the index or header name from the file
            $data[$systemField] = $row[$excelCol] ?? null;
        }

        return $data;
    }

    public function importRow(array $data): void
    {
        $payload = [
            'code' => strtoupper(trim((string) ($data['code'] ?? ''))),
            'name' => mb_substr(trim((string) ($data['name'] ?? '')), 0, 100),
            'units' => (int) ($data['units'] ?? 0),
            'has_practical' => $this->toBoolean($data['has_practical'] ?? false),
        ];

        if ($payload['code'] === '' || $payload['name'] === '' || $payload['units'] < 1) {
            throw new \InvalidArgumentException('بيانات المقرر غير مكتملة: الرمز والاسم والوحدات مطلوبة.');
        }

        Course::updateOrCreate(
            ['code' => $payload['code']],
            $payload
        );
    }

    private function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = mb_strtolower(trim((string) $value));

        return in_array($value, ['1', 'true', 'yes', 'y', 'عملي', 'نعم', 'به عملي'], true);
    }
}
