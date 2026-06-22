<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ImportRequest;
use App\ImportExport\Models\ImportExport;
use App\ImportExport\Services\ImportExportManager;
use App\ImportExport\Services\ImportService;
use Illuminate\Http\JsonResponse;

class ImportController extends Controller
{
    public function __construct(
        protected ImportService $importService,
        protected ImportExportManager $manager,
    ) {}

    public function import(string $module, ImportRequest $request): JsonResponse
    {
        if (!$this->manager->hasImporter($module)) {
            return response()->json(['error' => "No importer found for module: {$module}"], 404);
        }

        $importExport = $this->importService->import(
            module: $module,
            file: $request->file('file'),
            options: $request->input('options', []),
        );

        return response()->json([
            'id' => $importExport->id,
            'type' => 'import',
            'module' => $module,
            'status' => $importExport->status,
            'total_rows' => $importExport->total_rows,
            'message' => 'Import started. Use GET /api/import-export/' . $importExport->id . ' to track progress.',
        ], 201);
    }

    public function status(int $id): JsonResponse
    {
        $record = ImportExport::with(['sourceFile'])->findOrFail($id);

        if ($record->type !== 'import') {
            return response()->json(['error' => 'Record is not an import job.'], 400);
        }

        return response()->json([
            'id' => $record->id,
            'module' => $record->module,
            'status' => $record->status,
            'total_rows' => $record->total_rows,
            'processed_rows' => $record->processed_rows,
            'failed_rows' => $record->failed_rows,
            'error_message' => $record->error_message,
            'completed_at' => $record->completed_at,
            'created_at' => $record->created_at,
        ]);
    }
}
