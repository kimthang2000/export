<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_export_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_export_id')->constrained('import_exports')->cascadeOnDelete();
            $table->unsignedInteger('row_index')->nullable();
            $table->string('level', 10);
            $table->string('column', 50)->nullable();
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['import_export_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_export_logs');
    }
};
