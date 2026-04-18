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
        Schema::create('test_standards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_parameter_id')->constrained('test_parameters')->onDelete('cascade');
            $table->enum('standard_type', ['SNI', 'BPOM', 'FDA', 'EFSA', 'EU', 'WHO']); // Tipe standar
            $table->decimal('min_value', 18, 8)->nullable(); // Nilai minimum yang diizinkan
            $table->decimal('max_value', 18, 8)->nullable(); // Nilai maksimum yang diizinkan
            $table->text('requirement_description')->nullable(); // Deskripsi spesifik requirement
            $table->string('reference_document')->nullable(); // e.g., "SNI 16371:2019"
            $table->date('effective_date')->nullable(); // Tanggal berlaku
            $table->date('expired_date')->nullable(); // Tanggal kedaluwarsa standar
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index untuk performa query
            $table->index(['test_parameter_id', 'standard_type']);
            $table->index(['is_active', 'effective_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_standards');
    }
};
