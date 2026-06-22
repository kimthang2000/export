<?php

return [

    'import_readers' => [
        'csv' => App\ImportExport\Formats\Import\CsvImporter::class,
        'xlsx' => App\ImportExport\Formats\Import\ExcelImporter::class,
    ],

    'export_writers' => [
        'csv' => App\ImportExport\Formats\Export\CsvExporter::class,
        'xlsx' => App\ImportExport\Formats\Export\ExcelExporter::class,
    ],

];
