<?php

namespace Modules\Import\Imports;

use Modules\Import\Models\ImportJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

abstract class BaseImporter
{
    /**
     * Returns the schema definition for this import type.
     * Format: [ 'field_name' => [ 'label' => '...', 'type' => '...', 'required' => true, 'validation' => '...' ] ]
     */
    abstract public function getSchema(): array;

    /**
     * Transforms a raw row using user mapping into a validated data array.
     */
    abstract public function parseRow(array $row, array $mapping): array;

    /**
     * Actually creates or updates the record in the database.
     */
    abstract public function importRow(array $data): void;

    /**
     * Parses the file and returns a preview of the first N rows.
     */
    public function getPreviewData(ImportJob $job, int $limit = 20): array
    {
        $filePath = Storage::path($job->file_path);
        $rows = (new \Modules\Import\Imports\GenericDataImport)->read($filePath);

        return array_slice($rows, 0, $limit);
    }

    /**
     * Validates a set of rows against the schema and mapping.
     */
    public function validateRows(array $rows, array $mapping): array
    {
        $errors = [];
        $schema = $this->getSchema();
        $validationRules = [];
        $fieldLabels = [];

        foreach ($schema as $field => $definition) {
            if (! empty($definition['validation'])) {
                $validationRules[$field] = $definition['validation'];
            }

            $fieldLabels[$field] = $definition['label'] ?? $field;
        }

        foreach ($rows as $index => $row) {
            $parsed = $this->parseRow($row, $mapping);

            foreach ($schema as $field => $definition) {
                if (($definition['required'] ?? false) && ! array_key_exists($field, $parsed)) {
                    $parsed[$field] = null;
                }
            }

            $validator = Validator::make($parsed, $validationRules, [], $fieldLabels);

            if ($validator->fails()) {
                $errors[$index] = $validator->errors()->all();
            }
        }

        return $errors;
    }
}
