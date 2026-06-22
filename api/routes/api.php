<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh', [AuthController::class, 'refresh']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Import
    Route::post('/import/{module}', [App\Http\Controllers\Api\ImportController::class, 'import']);
    Route::get('/import/{module}/status/{id}', [App\Http\Controllers\Api\ImportController::class, 'status']);

    // Export
    Route::post('/export/{module}', [App\Http\Controllers\Api\ExportController::class, 'export']);
    Route::get('/export/{module}/preview', [App\Http\Controllers\Api\ExportController::class, 'preview']);
    Route::get('/export/{module}/status/{id}', [App\Http\Controllers\Api\ExportController::class, 'status']);

    // Generic
    Route::get('/import-export/modules', [App\Http\Controllers\Api\ImportExportController::class, 'modules']);
    Route::get('/import-export/{id}', [App\Http\Controllers\Api\ImportExportController::class, 'show']);
    Route::get('/import-export/{id}/download', [App\Http\Controllers\Api\ImportExportController::class, 'download']);
    Route::get('/import-export/{id}/logs', [App\Http\Controllers\Api\ImportExportController::class, 'logs']);
});
