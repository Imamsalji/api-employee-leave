<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leave_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')
                ->constrained('leave_requests')
                ->cascadeOnDelete();
            $table->string('file_name');          // nama asli file dari user
            $table->string('file_path');          // path storage relatif
            $table->string('file_type', 10);      // pdf, jpg, png
            $table->unsignedInteger('file_size'); // dalam bytes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_attachments');
    }
};
