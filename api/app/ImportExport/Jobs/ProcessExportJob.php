<?php

namespace App\ImportExport\Jobs;

use App\ImportExport\Models\ImportExport;
use App\ImportExport\Models\ImportExportFile;
use App\ImportExport\Services\ImportExportManager;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class ProcessExportJob implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public int $importExportId,
        public string $module,
        public string $format,
        public array $filters = [],
        public array $selectedColumns = [],
    ) {}

    public function handle(ImportExportManager $manager): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $importExport = ImportExport::findOrFail($this->importExportId);
        $exporter = $manager->getExporter($this->module);

        $writer = $this->getFormatWriter($this->format);

        $query = $exporter->query();
        foreach ($this->filters as $filter) {
            $exporter->applyFilter($query, $filter);
        }

        $totalRows = $query->count();
        $importExport->update(['total_rows' => $totalRows]);

        $fileName = $this->module . '_' . now()->format('Ymd_His') . '.' . $writer->extension();
        $filePath = "exports/{$this->module}/{$fileName}";
        $storagePath = Storage::path($filePath);

        $dir = dirname($storagePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $writer->openFile($storagePath, $this->selectedColumns);

        $query->lazy($exporter->chunkSize())->each(function ($row) use ($exporter, $writer) {
            $writer->writeRow($exporter->formatRow($row));
        });

        $writer->close();

        ImportExportFile::create([
            'import_export_id' => $this->importExportId,
            'type' => 'result',
            'disk' => config('filesystems.default'),
            'file_path' => $filePath,
            'original_name' => $fileName,
            'file_size' => file_exists($storagePath) ? filesize($storagePath) : 0,
            'mime_type' => $writer->contentType(),
            'extension' => $writer->extension(),
        ]);
    }

    protected function getFormatWriter(string $format): \App\ImportExport\Contracts\ExportFormat
    {
        $writers = config("import-export.export_writers", []);
        $class = $writers[$format] ?? throw new \InvalidArgumentException("No writer for format: {$format}");
        return app($class);
    }
}
