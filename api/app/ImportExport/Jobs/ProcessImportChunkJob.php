<?php

namespace App\ImportExport\Jobs;

use App\ImportExport\Contracts\ImportFormat;
use App\ImportExport\Models\ImportExport;
use App\ImportExport\Models\ImportExportLog;
use App\ImportExport\Services\ImportExportManager;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessImportChunkJob implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public int $importExportId,
        public string $module,
        public string $filePath,
        public string $extension,
        public int $offset,
        public int $limit,
        public array $options = [],
    ) {}

    public function handle(ImportExportManager $manager): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $importExport = ImportExport::findOrFail($this->importExportId);
        $importer = $manager->getImporter($this->module);

        $reader = $this->getFormatReader($this->extension);

        $processed = 0;
        $failed = 0;

        foreach ($reader->read($this->filePath, $this->offset, $this->limit) as $index => $row) {
            $rowIndex = $this->offset + $index + 1;

            $errors = $importer->validateRow($row, $rowIndex);
            if (!empty($errors)) {
                ImportExportLog::create([
                    'import_export_id' => $this->importExportId,
                    'row_index' => $rowIndex,
                    'level' => 'error',
                    'column' => array_key_first($errors),
                    'message' => implode('; ', $errors),
                    'context' => $row,
                ]);
                $failed++;
                continue;
            }

            try {
                $importer->processRow($row, $this->options);
                $processed++;
            } catch (\Throwable $e) {
                ImportExportLog::create([
                    'import_export_id' => $this->importExportId,
                    'row_index' => $rowIndex,
                    'level' => 'error',
                    'message' => $e->getMessage(),
                    'context' => $row,
                ]);
                $failed++;
            }
        }

        $importExport->increment('processed_rows', $processed);
        $importExport->increment('failed_rows', $failed);
    }

    protected function getFormatReader(string $extension): ImportFormat
    {
        $readers = config("import-export.import_readers", []);
        $class = $readers[$extension] ?? throw new \InvalidArgumentException("No reader for format: {$extension}");
        return app($class);
    }
}
