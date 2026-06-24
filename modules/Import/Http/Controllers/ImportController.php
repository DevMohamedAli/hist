<?php

namespace Modules\Import\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Import\Imports\CourseImporter;
use Modules\Import\Imports\GenericDataImport;
use Modules\Import\Imports\GradeImporter;
use Modules\Import\Imports\PdfCoursesImport;
use Modules\Import\Imports\StudentImporter;
use Modules\Import\Jobs\ProcessGradeWorkbookImport;
use Modules\Import\Models\ImportJob;
use Modules\Import\Services\GradeWorkbookImportService;
use Modules\Import\Services\ImportJobService;
use Modules\Import\Support\HeaderDetector;
use Modules\Shared\Http\Controllers\Controller;

class ImportController extends Controller
{
    public function __construct(protected ImportJobService $importJobService) {}

    public function index(): Response
    {
        return Inertia::render('Import/Index', [
            'defaultImportType' => 'students',
            'supportedTypes' => [
                ['value' => 'students', 'label' => 'الطلاب'],
                ['value' => 'courses', 'label' => 'المقررات الدراسية'],
                ['value' => 'grades', 'label' => 'النتائج الفصلية'],
                ['value' => 'grade_workbook', 'label' => 'كشف الدرجات متعدد الأوراق'],
            ],
        ]);
    }

    public function pdfCourses(): Response
    {
        return Inertia::render('Import/PdfCourses');
    }

    public function importPdfCourses(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf', 'max:20480'],
            'preview' => ['nullable', 'boolean'],
        ]);

        $path = $request->file('file')->store('imports/pdf-courses');
        $filePath = Storage::path($path);
        $importer = new PdfCoursesImport;

        try {
            if ($request->boolean('preview')) {
                return response()->json([
                    'preview' => $importer->previewFromPdf($filePath),
                ]);
            }

            return response()->json([
                'message' => 'تم استيراد مقررات PDF بنجاح.',
                'summary' => $importer->importFromPdf($filePath),
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

        if ($request->type === 'grade_workbook' && ! in_array($extension, ['xlsx', 'xls'], true)) {
            return response()->json([
                'message' => 'كشف الدرجات متعدد الأوراق يجب أن يكون ملف Excel بصيغة XLS أو XLSX.',
            ], 422);
        }

        $job = $this->importJobService->createJob($request->type, $request->file('file'));

        try {
            if ($job->type === 'grade_workbook') {
                return $this->uploadGradeWorkbook($job);
            }

            $filePath = Storage::path($job->file_path);
            $rows = (new GenericDataImport)->read($filePath);
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
        } catch (\Throwable $exception) {
            Log::warning('Import upload preview failed', [
                'job_id' => $job->id,
                'type' => $job->type,
                'exception' => $exception->getMessage(),
            ]);

            return response()->json(['job_id' => $job->id, 'columns' => [], 'headerRowIndex' => 0], 200);
        }
    }

    public function preview(int $jobId)
    {
        $job = ImportJob::findOrFail($jobId);

        if ($job->type === 'grade_workbook') {
            return response()->json([
                'columns' => [],
                'data' => [],
                'schema' => [],
                'workbook' => app(GradeWorkbookImportService::class)->preview(Storage::path($job->file_path)),
            ]);
        }

        $importer = $this->resolveImporter($job->type);

        return response()->json([
            'columns' => $job->original_columns,
            'data' => $this->getContentRows($job),
            'schema' => $importer->getSchema(),
        ]);
    }

    public function validate(Request $request, int $jobId)
    {
        $job = ImportJob::findOrFail($jobId);

        if ($job->type === 'grade_workbook') {
            $preview = app(GradeWorkbookImportService::class)->preview(Storage::path($job->file_path));
            $this->storeWorkbookPreview($job, $preview);
            $valid = ($preview['summary']['sheets'] ?? 0) > 0
                && ($preview['summary']['grade_cells'] ?? 0) > 0
                && $this->hasUsableWorkbookMetadata($preview);

            return response()->json([
                'valid' => $valid,
                'errors' => $valid ? [] : ['workbook' => ['لم يتم العثور على أوراق درجات قابلة للاستيراد أو بياناتها التعريفية غير مكتملة.']],
                'warnings' => $preview['warnings'],
                'summary' => $preview['summary'],
            ]);
        }

        $request->validate([
            'mapping' => 'required|array',
        ]);

        $errors = $this->resolveImporter($job->type)
            ->validateRows($this->getContentRows($job), $request->mapping);

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

        if ($job->type === 'grade_workbook') {
            $preview = app(GradeWorkbookImportService::class)->preview(Storage::path($job->file_path));

            if (
                ($preview['summary']['sheets'] ?? 0) === 0
                || ($preview['summary']['grade_cells'] ?? 0) === 0
                || ! $this->hasUsableWorkbookMetadata($preview)
            ) {
                $this->storeWorkbookPreview($job, $preview);

                return response()->json([
                    'message' => 'لم يتم العثور على أوراق درجات قابلة للاستيراد أو بياناتها التعريفية غير مكتملة.',
                    'summary' => $preview['summary'],
                ], 422);
            }

            $job->update([
                'status' => 'queued',
                'processed_rows' => 0,
                'errors' => null,
            ]);

            ProcessGradeWorkbookImport::dispatch($job->id);

            return response()->json(['message' => 'تم إرسال استيراد كشف الدرجات إلى قائمة الانتظار.']);
        }

        $mapping = $request->mapping ?? $job->mapping;

        if (! $mapping) {
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
                $importer->importRow($importer->parseRow($row, $mapping));
                $this->importJobService->updateProgress($job->id, $index + 1, $total);
            }

            $job->update(['status' => 'completed']);

            return response()->json(['message' => 'تم استيراد البيانات بنجاح.']);
        } catch (\Throwable $exception) {
            $job->update(['status' => 'failed', 'errors' => [$exception->getMessage()]]);

            return response()->json([
                'message' => 'تعذر تنفيذ الاستيراد.',
                'error' => $exception->getMessage(),
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
            'courses' => new CourseImporter,
            'students' => new StudentImporter,
            'grades' => new GradeImporter,
            default => throw new \Exception("Unsupported import type: $type"),
        };
    }

    protected function getContentRows(ImportJob $job): array
    {
        $rows = (new GenericDataImport)->read(Storage::path($job->file_path));
        $headers = $job->original_columns ?? HeaderDetector::extractHeaders($rows[0] ?? []);
        $headerRowIndex = $job->header_row_index ?? HeaderDetector::detectHeaderRow($rows);
        $contentRows = array_slice($rows, $headerRowIndex + 1);

        return array_map(
            fn (array $row): array => $this->combineHeadersWithRow($headers, $row),
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

    private function uploadGradeWorkbook(ImportJob $job)
    {
        $preview = app(GradeWorkbookImportService::class)->preview(Storage::path($job->file_path));
        $this->storeWorkbookPreview($job, $preview);

        return response()->json([
            'job_id' => $job->id,
            'type' => $job->type,
            'columns' => [],
            'workbook' => $preview,
        ]);
    }

    private function storeWorkbookPreview(ImportJob $job, array $preview): void
    {
        $job->update([
            'original_columns' => array_column($preview['sheets'], 'name'),
            'total_rows' => $preview['summary']['grade_cells'] ?? 0,
            'errors' => [
                'summary' => $preview['summary'],
                'warnings' => $preview['warnings'],
            ],
        ]);
    }

    private function hasUsableWorkbookMetadata(array $preview): bool
    {
        foreach ($preview['sheets'] as $sheet) {
            if (
                ($sheet['metadata']['metadata_complete'] ?? false)
                || ! str_starts_with((string) $sheet['metadata']['department'], 'Imported ')
                || ! str_starts_with((string) $sheet['metadata']['specialization'], 'Imported ')
            ) {
                return true;
            }
        }

        return false;
    }
}
