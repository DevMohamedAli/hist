<?php

namespace Modules\Import\Services;

use Modules\Import\Models\ImportJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Exception;

class ImportJobService
{
    /**
     * Create a new import job after file upload.
     */
    public function createJob(string $type, \Illuminate\Http\UploadedFile $file): ImportJob
    {
        $path = $file->store('imports');

        return ImportJob::create([
            'user_id' => Auth::id(),
            'type' => $type,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'status' => 'pending',
        ]);
    }

    /**
     * Update job status and progress.
     */
    public function updateProgress(int $jobId, int $processed, int $total, ?string $status = null, ?array $errors = null): void
    {
        $job = ImportJob::findOrFail($jobId);
        $job->update([
            'processed_rows' => $processed,
            'total_rows' => $total,
            'status' => $status ?? $job->status,
            'errors' => $errors ?? $job->errors,
        ]);
    }

    /**
     * Update the column mapping for a job.
     */
    public function updateMapping(int $jobId, array $mapping): void
    {
        $job = ImportJob::findOrFail($jobId);
        $job->update([
            'mapping' => $mapping,
            'status' => 'ready',
        ]);
    }

    /**
     * Cancel an ongoing import job.
     */
    public function cancelJob(int $jobId): void
    {
        $job = ImportJob::findOrFail($jobId);
        $job->update(['status' => 'failed']);
        // In a real scenario, we would also signal the background queue job to stop.
    }

    /**
     * Get job details for progress tracking.
     */
    public function getJobStatus(int $jobId): array
    {
        $job = ImportJob::findOrFail($jobId);
        return [
            'status' => $job->status,
            'progress' => $job->total_rows > 0 ? round(($job->processed_rows / $job->total_rows) * 100) : 0,
            'processed' => $job->processed_rows,
            'total' => $job->total_rows,
            'errors' => $job->errors,
        ];
    }
}
