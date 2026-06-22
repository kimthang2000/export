<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ExportRequest;
use App\ImportExport\Models\ImportExport;
use App\ImportExport\Services\ExportService;
use App\ImportExport\Services\ImportExportManager;
use Illuminate\Http\JsonResponse;

class ExportController extends Controller
{
    public function __construct(
        protected ExportService $exportService,
        protected ImportExportManager $manager,
    ) {}

    public function export(string $module, ExportRequest $request): JsonResponse
    {
        if (!$this->manager->hasExporter($module)) {
            return response()->json(['error' => "No exporter found for module: {$module}"], 404);
        }

        $importExport = $this->exportService->export(
            module: $module,
            format: $request->input('format', 'csv'),
            filters: $request->input('filters', []),
            columns: $request->input('columns'),
        );

        return response()->json([
            'id' => $importExport->id,
            'type' => 'export',
            'module' => $module,
            'status' => $importExport->status,
            'message' => 'Export started. Use GET /api/import-export/' . $importExport->id . ' to track progress.',
        ], 201);
    }

    public function status(int $id): JsonResponse
    {
        $record = ImportExport::with(['resultFile'])->findOrFail($id);

        if ($record->type !== 'export') {
            return response()->json(['error' => 'Record is not an export job.'], 400);
        }

        return response()->json([
            'id' => $record->id,
            'module' => $record->module,
            'status' => $record->status,
            'format' => $record->file_format,
            'total_rows' => $record->total_rows,
            'completed_at' => $record->completed_at,
            'created_at' => $record->created_at,
        ]);
    }

    public function preview(string $module, ExportRequest $request): JsonResponse
    {
        if (!$this->manager->hasExporter($module)) {
            return response()->json(['error' => "No exporter found for module: {$module}"], 404);
        }

        $result = $this->exportService->preview(
            module: $module,
            format: $request->input('format', 'csv'),
            filters: $request->input('filters', []),
            columns: $request->input('columns'),
        );

        return response()->json($result);
    }
}
