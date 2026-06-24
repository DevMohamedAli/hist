<?php

namespace Modules\Academic\Console\Commands;

use Illuminate\Console\Command;
use Modules\Academic\Services\AcademicAuditService;

class AcademicAuditCommand extends Command
{
    protected $signature = 'academic:audit {--json : Output the audit as JSON}';

    protected $description = 'Report academic core data integrity and seeded lifecycle readiness.';

    public function handle(): int
    {
        $report = app(AcademicAuditService::class)->report();

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        $this->info('Academic audit report');
        $this->table(['Metric', 'Value'], collect($report['seed_smoke'])->map(
            fn ($value, $key) => [$key, is_bool($value) ? ($value ? 'yes' : 'no') : $value]
        )->values()->all());

        $this->newLine();
        $this->info('Integrity findings');
        $this->table(['Check', 'Count'], collect($report['checks'])->map(
            fn ($items, $key) => [$key, count($items)]
        )->values()->all());

        if ($report['has_issues']) {
            $this->warn('Issues were found. Re-run with --json for row-level details.');
        } else {
            $this->info('No blocking academic integrity issues were found.');
        }

        return self::SUCCESS;
    }
}
