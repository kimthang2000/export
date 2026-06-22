<?php

namespace App\ImportExport\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ImportExport extends Model
{
    protected $table = 'import_exports';

    protected $fillable = [
        'type', 'module', 'status', 'file_format', 'options',
        'total_rows', 'processed_rows', 'failed_rows', 'warning_rows',
        'error_message', 'created_by', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'json',
            'completed_at' => 'datetime',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function files()
    {
        return $this->hasMany(ImportExportFile::class, 'import_export_id');
    }

    public function logs()
    {
        return $this->hasMany(ImportExportLog::class, 'import_export_id');
    }

    public function sourceFile()
    {
        return $this->hasOne(ImportExportFile::class, 'import_export_id')->where('type', 'source');
    }

    public function resultFile()
    {
        return $this->hasOne(ImportExportFile::class, 'import_export_id')->where('type', 'result');
    }

    public function errorFile()
    {
        return $this->hasOne(ImportExportFile::class, 'import_export_id')->where('type', 'error');
    }
}
