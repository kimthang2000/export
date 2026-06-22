<?php

namespace App\ImportExport\Models;

use Illuminate\Database\Eloquent\Model;

class ImportExportLog extends Model
{
    protected $table = 'import_export_logs';

    protected $fillable = [
        'import_export_id', 'row_index', 'level', 'column', 'message', 'context',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'json',
        ];
    }

    public function importExport()
    {
        return $this->belongsTo(ImportExport::class, 'import_export_id');
    }
}
