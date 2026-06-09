<?php

namespace Modules\Import\Imports;

use Modules\Import\Support\SpreadsheetReader;

class GenericDataImport
{
    public function __construct(private readonly SpreadsheetReader $reader = new SpreadsheetReader())
    {
    }

    public function read(string $filePath): array
    {
        return $this->reader->read($filePath);
    }
}
