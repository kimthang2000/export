<?php

namespace App\ImportExport\Services;

use App\ImportExport\Contracts\Importable;
use App\ImportExport\Contracts\Exportable;

class ImportExportManager
{
    protected array $importers = [];
    protected array $exporters = [];

    public function register(string $module, ?Importable $importable = null, ?Exportable $exportable = null): void
    {
        if ($importable) {
            $this->importers[$module] = $importable;
        }
        if ($exportable) {
            $this->exporters[$module] = $exportable;
        }
    }

    public function getImporter(string $module): Importable
    {
        if (!isset($this->importers[$module])) {
            throw new \InvalidArgumentException("No importer registered for module: {$module}");
        }
        return $this->importers[$module];
    }

    public function getExporter(string $module): Exportable
    {
        if (!isset($this->exporters[$module])) {
            throw new \InvalidArgumentException("No exporter registered for module: {$module}");
        }
        return $this->exporters[$module];
    }

    public function hasImporter(string $module): bool
    {
        return isset($this->importers[$module]);
    }

    public function hasExporter(string $module): bool
    {
        return isset($this->exporters[$module]);
    }

    public function allModules(): array
    {
        return array_unique(array_merge(array_keys($this->importers), array_keys($this->exporters)));
    }
}
