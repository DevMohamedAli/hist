<?php

namespace Modules\Import\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Modules\Import\Services\ConsolidatedStudentCoursesImportService;

class ImportConsolidatedStudentCoursesCommand extends Command
{
    protected $signature = 'import:consolidated-student-courses
        {file : Path to the consolidated workbook}
        {--dry-run : Parse and summarize without writing to the database}
        {--report=consolidated-student-courses-import-report.json : Report path under storage/app/private}';

    protected $description = 'Import the consolidated student courses workbook.';

    public function handle(ConsolidatedStudentCoursesImportService $service): int
    {
        $file = (string) $this->argument('file');

        if (! is_file($file)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        $result = $this->option('dry-run')
            ? $service->dryRun($file)
            : $service->import($file);

        $report = [
            'mode' => $this->option('dry-run') ? 'dry-run' : 'import',
            'file' => $file,
            'generated_at' => now()->toDateTimeString(),
            ...$result,
        ];

        Storage::put((string) $this->option('report'), json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->line(json_encode($report['summary'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info('Report: '.storage_path('app/private/'.$this->option('report')));

        return self::SUCCESS;
    }
}
