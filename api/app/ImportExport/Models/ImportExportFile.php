<?php

namespace App\ImportExport\Models;

use Illuminate\Database\Eloquent\Model;

class ImportExportFile extends Model
{
    protected $table = 'import_export_files';

    protected $fillable = [
        'import_export_id', 'type', 'disk', 'file_path',
        'original_name', 'file_size', 'mime_type', 'extension',
    ];

    public function importExport()
    {
        return $this->belongsTo(ImportExport::class, 'import_export_id');
    }
}
