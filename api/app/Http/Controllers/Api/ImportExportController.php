<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\ImportExport\Models\ImportExport;
use App\ImportExport\Models\ImportExportLog;
use App\ImportExport\Services\ImportExportManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImportExportController extends Controller
{
    public function show(int $id): JsonResponse
    {
        $record = ImportExport::with(['sourceFile', 'resultFile', 'errorFile'])->findOrFail($id);

        $data = [
            'id' => $record->id,
            'type' => $record->type,
            'module' => $record->module,
            'status' => $record->status,
            'file_format' => $record->file_format,
            'total_rows' => $record->total_rows,
            'processed_rows' => $record->processed_rows,
            'failed_rows' => $record->failed_rows,
            'warning_rows' => $record->warning_rows,
            'error_message' => $record->error_message,
            'created_at' => $record->created_at,
            'completed_at' => $record->completed_at,
        ];

        if ($record->relationLoaded('sourceFile') && $record->sourceFile) {
            $data['source_file'] = [
                'original_name' => $record->sourceFile->original_name,
                'size' => $record->sourceFile->file_size,
            ];
        }

        if ($record->relationLoaded('resultFile') && $record->resultFile) {
            $data['result_file'] = [
                'original_name' => $record->resultFile->original_name,
                'size' => $record->resultFile->file_size,
            ];
        }

        return response()->json($data);
    }

    public function download(int $id, Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
    {
        $record = ImportExport::findOrFail($id);
        $type = $request->input('type', 'result');

        $file = $record->files()->where('type', $type)->first();
        if (!$file) {
            return response()->json(['error' => "No {$type} file found."], 404);
        }

        $fullPath = Storage::path($file->file_path);
        if (!file_exists($fullPath)) {
            return response()->json(['error' => 'File not found on disk.'], 404);
        }

        return response()->download($fullPath, $file->original_name);
    }

    public function logs(int $id, Request $request): JsonResponse
    {
        $record = ImportExport::findOrFail($id);

        $query = ImportExportLog::where('import_export_id', $id);

        if ($request->has('level')) {
            $query->where('level', $request->input('level'));
        }

        $logs = $query->orderBy('row_index')->paginate($request->input('per_page', 50));

        return response()->json($logs);
    }

    public function modules(): JsonResponse
    {
        $manager = app(ImportExportManager::class);
        $modules = [];

        foreach ($manager->allModules() as $module) {
            $modules[$module] = [
                'importable' => $manager->hasImporter($module),
                'exportable' => $manager->hasExporter($module),
            ];
        }

        return response()->json($modules);
    }
}
