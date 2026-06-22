<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_exports', function (Blueprint $table) {
            $table->id();
            $table->string('type', 10);
            $table->string('module', 50);
            $table->string('status', 20)->default('pending');
            $table->string('file_format', 10);
            $table->json('options')->nullable();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->unsignedInteger('warning_rows')->default(0);
            $table->text('error_message')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_exports');
    }
};
