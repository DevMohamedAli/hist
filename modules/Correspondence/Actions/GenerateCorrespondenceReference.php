<?php

namespace Modules\Correspondence\Actions;

use Modules\Correspondence\Models\CorrespondenceReferenceSequence;

class GenerateCorrespondenceReference
{
    public function execute(?int $year = null): string
    {
        $year ??= (int) now()->format('Y');

        $sequence = CorrespondenceReferenceSequence::query()->lockForUpdate()->firstOrCreate(
            ['year' => $year],
            ['last_number' => 0],
        );

        $sequence->increment('last_number');

        return strtr(config('correspondence.reference_format', 'COR-{YEAR}-{SEQUENCE}'), [
            '{MODULE}' => 'COR',
            '{YEAR}' => (string) $year,
            '{SEQUENCE}' => str_pad((string) $sequence->last_number, 6, '0', STR_PAD_LEFT),
        ]);
    }
}
