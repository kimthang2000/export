<?php

namespace App\ImportExport\Events;

use App\ImportExport\Models\ImportExport;
use Illuminate\Foundation\Events\Dispatchable;

class ExportCompleted
{
    use Dispatchable;

    public function __construct(
        public ImportExport $importExport,
    ) {}
}
