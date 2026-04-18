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
        Schema::create('test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_session_id')->constrained('test_sessions')->onDelete('cascade');
            $table->foreignId('test_parameter_id')->constrained('test_parameters')->onDelete('restrict');
            
            // Data numerik dengan presisi tinggi
            $table->decimal('measured_value', 18, 8); // Nilai hasil pengukuran
            $table->string('unit'); // Satuan (dari test_parameter)
            
            // Threshold yang digunakan untuk perbandingan
            $table->foreignId('applied_standard_id')->nullable()->constrained('test_standards')->onDelete('set null');
            $table->decimal('standard_min_value', 18, 8)->nullable(); // Min value dari standard saat penyimpanan
            $table->decimal('standard_max_value', 18, 8)->nullable(); // Max value dari standard saat penyimpanan
            
            // Hasil perbandingan otomatis (PASS/FAIL)
            $table->enum('result_status', ['PASS', 'FAIL', 'INCONCLUSIVE'])->nullable();
            $table->decimal('deviation_percentage', 10, 4)->nullable(); // Persentase penyimpangan
            
            // Status data dalam workflow
            $table->enum('data_status', ['Alat Siap', 'In-Progress', 'Draft', 'Verified'])->default('Draft');
            
            // Notes dan metadata
            $table->text('notes')->nullable();
            $table->json('calculation_details')->nullable(); // Simpan detail perhitungan untuk audit trail
            
            $table->timestamps();

            // Indexes untuk performa
            $table->index(['test_session_id', 'result_status']);
            $table->index(['test_parameter_id', 'data_status']);
            $table->index('result_status');
            $table->unique(['test_session_id', 'test_parameter_id']); // Satu parameter per session
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_results');
    }
};
