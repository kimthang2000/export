<?php

namespace App\Providers;

use App\ImportExport\Formats\Import\CsvImporter;
use App\ImportExport\Formats\Import\ExcelImporter;
use App\ImportExport\Formats\Export\CsvExporter;
use App\ImportExport\Formats\Export\ExcelExporter;
use App\ImportExport\Modules\UserExport;
use App\ImportExport\Modules\UserImport;
use App\ImportExport\Services\ExportService;
use App\ImportExport\Services\ImportExportManager;
use App\ImportExport\Services\ImportService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ImportExportManager::class);
        $this->app->singleton(ImportService::class);
        $this->app->singleton(ExportService::class);
    }

    public function boot(): void
    {
        $manager = $this->app->make(ImportExportManager::class);

        // Register format readers
        $importService = $this->app->make(ImportService::class);
        $importService->addFormatReader('csv', new CsvImporter);
        $importService->addFormatReader('xlsx', new ExcelImporter);

        // Register format writers
        $exportService = $this->app->make(ExportService::class);
        $exportService->addFormatWriter('csv', new CsvExporter);
        $exportService->addFormatWriter('xlsx', new ExcelExporter);

        // Register example modules (remove/replace in production)
        $manager->register('users', new UserImport, new UserExport);
    }
}
