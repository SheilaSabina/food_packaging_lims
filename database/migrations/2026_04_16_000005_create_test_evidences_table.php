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
        Schema::create('test_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_result_id')->nullable()->constrained('test_results')->onDelete('cascade');
            $table->foreignId('test_session_id')->nullable()->constrained('test_sessions')->onDelete('cascade');
            
            $table->string('file_name');
            $table->string('file_path'); // Path ke storage
            $table->string('file_type'); // e.g., 'image/jpeg', 'image/png'
            $table->bigInteger('file_size'); // Ukuran file dalam bytes
            
            $table->enum('evidence_type', ['Test Photo', 'Equipment Status', 'Calibration Certificate', 'Other'])->default('Test Photo');
            $table->text('description')->nullable();
            
            $table->string('uploaded_by')->nullable(); // Nama teknisi yang upload
            $table->dateTime('uploaded_at')->default(now());
            
            // Metadata
            $table->json('metadata')->nullable(); // EXIF data atau metadata lainnya
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->index(['test_result_id', 'evidence_type']);
            $table->index(['test_session_id', 'is_verified']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_evidences');
    }
};
