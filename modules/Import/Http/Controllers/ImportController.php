<?php

namespace Modules\Import\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Import\Imports\GenericDataImport;
use Modules\Import\Imports\PdfCoursesImport;
use Modules\Import\Models\ImportJob;
use Modules\Import\Services\ImportJobService;
use Modules\Import\Support\HeaderDetector;
use Modules\Shared\Http\Controllers\Controller;

class ImportController extends Controller
{
    protected ImportJobService $importJobService;

    public function __construct(ImportJobService $importJobService)
    {
        $this->importJobService = $importJobService;
    }

    public function index(): Response
    {
        return Inertia::render('Import/Index', [
            'defaultImportType' => 'students',
            'supportedTypes' => [
                ['value' => 'students', 'label' => 'الطلاب'],
                ['value' => 'courses', 'label' => 'المقررات الدراسية'],
                ['value' => 'grades', 'label' => 'النتائج الفصلية'],
            ],
        ]);
    }

    public function pdfCourses(): Response
    {
        return Inertia::render('Import/PdfCourses');
    }

    public function importPdfCourses(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:pdf', 'max:20480'],
            'preview' => ['nullable', 'boolean'],
        ]);

        $path = $request->file('file')->store('imports/pdf-courses');
        $filePath = Storage::path($path);
        $importer = new PdfCoursesImport();

        try {
            if ($request->boolean('preview')) {
                return response()->json([
                    'preview' => $importer->previewFromPdf($filePath),
                ]);
            }

            $summary = $importer->importFromPdf($filePath);

            return response()->json([
                'message' => 'تم استيراد مقررات PDF بنجاح.',
                'summary' => $summary,
            ]);
        } catch (\Throwable $exception) {
            Log::error('PDF courses import failed', [
                'path' => $path,
                'exception' => $exception,
            ]);

            return response()->json([
                'message' => 'تعذر استيراد ملف PDF.',
                'error' => $exception->getMessage(),
            ], 422);
        }
    }

    public function upload(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'file' => 'required|file',
        ]);

        $extension = strtolower($request->file('file')->getClientOriginalExtension());

        if (! in_array($extension, ['xlsx', 'xls', 'csv'], true)) {
            return response()->json(['error' => 'Unsupported import file type.'], 422);
        }

        $job = $this->importJobService->createJob($request->type, $request->file('file'));

        try {
            // Detect columns for preview
            $filePath = Storage::path($job->file_path);
            $rows = (new GenericDataImport)->read($filePath);

            // Smart header detection: find the row with the most non-empty cells
            $headerRowIndex = HeaderDetector::detectHeaderRow($rows);
            $headers = HeaderDetector::extractHeaders($rows[$headerRowIndex] ?? []);

            $job->update([
                'original_columns' => $headers,
                'header_row_index' => $headerRowIndex,
            ]);

            return response()->json([
                'job_id' => $job->id,
                'columns' => $headers,
                'headerRowIndex' => $headerRowIndex,
            ]);
        } catch (\Exception $e) {
            // If file reading fails, still return job ID but with empty columns
            // User can proceed and columns will be detected on preview
            return response()->json(['job_id' => $job->id, 'columns' => [], 'headerRowIndex' => 0], 200);
        }
    }

    public function preview(int $jobId)
    {
        $job = ImportJob::findOrFail($jobId);

        // Resolve importer based on type
        $importer = $this->resolveImporter($job->type);
        $previewData = $this->getContentRows($job);

        return response()->json([
            'columns' => $job->original_columns,
            'data' => $previewData,
            'schema' => $importer->getSchema(),
        ]);
    }

    public function validate(Request $request, int $jobId)
    {
        $request->validate([
            'mapping' => 'required|array',
        ]);

        $job = ImportJob::findOrFail($jobId);
        $importer = $this->resolveImporter($job->type);

        $contentRows = $this->getContentRows($job);

        $errors = $importer->validateRows($contentRows, $request->mapping);

        return response()->json([
            'valid' => empty($errors),
            'errors' => $errors,
        ]);
    }

    public function execute(Request $request, int $jobId)
    {
        $request->validate([
            'mapping' => ['nullable', 'array'],
            'selected_rows' => ['nullable', 'array'],
            'selected_rows.*' => ['boolean'],
        ]);

        $job = ImportJob::findOrFail($jobId);
        $mapping = $request->mapping ?? $job->mapping;

        if (!$mapping) {
            return response()->json(['message' => 'ربط الحقول مطلوب قبل تنفيذ الاستيراد.'], 400);
        }

        $importer = $this->resolveImporter($job->type);

        $job->update([
            'mapping' => $mapping,
            'status' => 'importing',
        ]);

        try {
            $contentRows = $this->filterSelectedRows(
                $this->getContentRows($job),
                $request->input('selected_rows', []),
            );

            $total = count($contentRows);

            if ($total === 0) {
                throw new \InvalidArgumentException('لم يتم تحديد أي صفوف للاستيراد.');
            }

            foreach ($contentRows as $index => $row) {
                $parsed = $importer->parseRow($row, $mapping);
                $importer->importRow($parsed);

                $this->importJobService->updateProgress($job->id, $index + 1, $total);
            }

            $job->update(['status' => 'completed']);
            return response()->json(['message' => 'تم استيراد البيانات بنجاح.']);
        } catch (\Throwable $e) {
            $job->update(['status' => 'failed', 'errors' => [$e->getMessage()]]);
            return response()->json([
                'message' => 'تعذر تنفيذ الاستيراد.',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function progress(int $jobId)
    {
        return response()->json($this->importJobService->getJobStatus($jobId));
    }

    public function cancel(int $jobId)
    {
        $this->importJobService->cancelJob($jobId);
        return response()->json(['message' => 'Import cancelled']);
    }

    protected function resolveImporter(string $type)
    {
        return match ($type) {
            'courses' => new \Modules\Import\Imports\CourseImporter(),
            'students' => new \Modules\Import\Imports\StudentImporter(),
            'grades' => new \Modules\Import\Imports\GradeImporter(),
            default => throw new \Exception("Unsupported import type: $type"),
        };
    }

    protected function getContentRows(ImportJob $job): array
    {
        $filePath = Storage::path($job->file_path);
        $rows = (new GenericDataImport)->read($filePath);
        $headers = $job->original_columns ?? HeaderDetector::extractHeaders($rows[0] ?? []);

        // Use the stored header row index, or detect it if not stored
        $headerRowIndex = $job->header_row_index ?? HeaderDetector::detectHeaderRow($rows);
        $contentRows = array_slice($rows, $headerRowIndex + 1);

        return array_map(
            fn(array $row): array => $this->combineHeadersWithRow($headers, $row),
            $contentRows,
        );
    }

    protected function combineHeadersWithRow(array $headers, array $row): array
    {
        $combined = [];

        foreach ($headers as $index => $header) {
            if ($header === null || $header === '') {
                continue;
            }

            $combined[(string) $header] = $row[$index] ?? null;
        }

        return $combined;
    }

    protected function filterSelectedRows(array $rows, array $selectedRows): array
    {
        if ($selectedRows === []) {
            return $rows;
        }

        return array_values(array_filter(
            $rows,
            fn (array $_row, int $index): bool => (bool) ($selectedRows[$index] ?? false),
            ARRAY_FILTER_USE_BOTH,
        ));
    }
}
