<?php

namespace App\ImportExport\Services;

use App\ImportExport\Contracts\ImportFormat;
use App\ImportExport\Models\ImportExport;
use App\ImportExport\Models\ImportExportFile;
use App\ImportExport\Jobs\ProcessImportChunkJob;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

class ImportService
{
    protected ImportExportManager $manager;

    protected array $formatReaders = [];

    public function __construct(ImportExportManager $manager)
    {
        $this->manager = $manager;
    }

    public function addFormatReader(string $extension, ImportFormat $reader): void
    {
        $this->formatReaders[$extension] = $reader;
    }

    public function import(string $module, UploadedFile $file, array $options = []): ImportExport
    {
        $importer = $this->manager->getImporter($module);
        $extension = strtolower($file->getClientOriginalExtension());
        $reader = $this->formatReaders[$extension]
            ?? throw new \InvalidArgumentException("Unsupported format: {$extension}");

        $importExport = ImportExport::create([
            'type' => 'import',
            'module' => $module,
            'status' => 'pending',
            'file_format' => $extension,
            'options' => $options,
            'created_by' => auth()->id(),
        ]);

        $path = $file->store("imports/{$module}");

        ImportExportFile::create([
            'import_export_id' => $importExport->id,
            'type' => 'source',
            'disk' => config('filesystems.default'),
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'extension' => $extension,
        ]);

        $storagePath = Storage::path($path);
        $totalRows = $reader->countRows($storagePath);

        $importExport->update(['total_rows' => $totalRows]);

        if ($totalRows === 0) {
            $importExport->update(['status' => 'completed', 'completed_at' => now()]);
            return $importExport;
        }

        $importExport->update(['status' => 'processing']);
        $chunkSize = $importer->chunkSize();
        $chunks = (int)ceil($totalRows / $chunkSize);

        $jobs = [];
        for ($i = 0; $i < $chunks; $i++) {
            $jobs[] = new ProcessImportChunkJob(
                importExportId: $importExport->id,
                module: $module,
                filePath: $storagePath,
                extension: $extension,
                offset: $i * $chunkSize,
                limit: $chunkSize,
                options: $options,
            );
        }

        Bus::batch($jobs)
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

        return $importExport;
    }
}
