<?php

namespace Modules\Import\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Import\Models\ImportJob;
use Modules\Import\Services\GradeWorkbookImportService;

class ProcessGradeWorkbookImport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $importJobId) {}

    public function handle(GradeWorkbookImportService $service): void
    {
        $job = ImportJob::findOrFail($this->importJobId);

        $job->update([
            'status' => 'importing',
            'errors' => null,
        ]);

        try {
            $result = $service->import($job->fresh());

            $job->fresh()->update([
                'status' => 'completed',
                'processed_rows' => $result['summary']['imported_grades'] ?? $job->processed_rows,
                'total_rows' => $result['summary']['grade_cells'] ?? $job->total_rows,
                'errors' => [
                    'summary' => $result['summary'],
                    'warnings' => $result['warnings'],
                ],
            ]);
        } catch (\Throwable $exception) {
            $job->fresh()->update([
                'status' => 'failed',
                'errors' => [[
                    'message' => $exception->getMessage(),
                ]],
            ]);

            throw $exception;
        }
    }
}
