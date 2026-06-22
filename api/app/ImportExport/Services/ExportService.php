<?php

namespace App\ImportExport\Services;

use App\ImportExport\Contracts\ExportFormat;
use App\ImportExport\Models\ImportExport;
use App\ImportExport\Models\ImportExportFile;
use App\ImportExport\Jobs\ProcessExportJob;
use Illuminate\Support\Facades\Bus;

class ExportService
{
    protected ImportExportManager $manager;

    protected array $formatWriters = [];

    public function __construct(ImportExportManager $manager)
    {
        $this->manager = $manager;
    }

    public function addFormatWriter(string $extension, ExportFormat $writer): void
    {
        $this->formatWriters[$extension] = $writer;
    }

    public function export(string $module, string $format = 'csv', array $filters = [], ?array $columns = null): ImportExport
    {
        $exporter = $this->manager->getExporter($module);
        $writer = $this->formatWriters[$format]
            ?? throw new \InvalidArgumentException("Unsupported format: {$format}");

        $selectedColumns = $columns ? array_intersect_key($exporter->columns(), array_flip($columns)) : $exporter->columns();

        $importExport = ImportExport::create([
            'type' => 'export',
            'module' => $module,
            'status' => 'pending',
            'file_format' => $format,
            'options' => ['filters' => $filters, 'columns' => $selectedColumns],
            'created_by' => auth()->id(),
        ]);

        Bus::batch([
            new ProcessExportJob(
                importExportId: $importExport->id,
                module: $module,
                format: $format,
                filters: $filters,
                selectedColumns: $selectedColumns,
            ),
        ])
            ->finally(function () use ($importExport) {
                $importExport->refresh();
                if ($importExport->status === 'processing') {
                    $importExport->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);
                }
            })
            ->dispatch();

        $importExport->update(['status' => 'processing']);

        return $importExport;
    }

    public function preview(string $module, string $format = 'csv', array $filters = [], ?array $columns = null): array
    {
        $exporter = $this->manager->getExporter($module);
        $query = $exporter->query();

        foreach ($filters as $filter) {
            $exporter->applyFilter($query, $filter);
        }

        $selectedColumns = $columns ? array_intersect_key($exporter->columns(), array_flip($columns)) : $exporter->columns();

        $rows = $query->limit(5)->get()->map(fn($model) => $exporter->formatRow($model));

        return [
            'columns' => $selectedColumns,
            'rows' => $rows,
            'total' => $query->count(),
        ];
    }
}
