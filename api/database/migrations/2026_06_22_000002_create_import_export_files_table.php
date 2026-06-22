<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_export_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_export_id')->constrained('import_exports')->cascadeOnDelete();
            $table->string('type', 10);
            $table->string('disk', 20)->default('local');
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->string('extension', 10)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_export_files');
    }
};
