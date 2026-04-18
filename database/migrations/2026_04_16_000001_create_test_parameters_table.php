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
        Schema::create('test_parameters', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Migrasi Total", "Kadar BPA", "Konsentrasi Radon"
            $table->text('description')->nullable();
            $table->string('unit'); // e.g., "mg/dm2", "µg/L", "Bq/L"
            $table->enum('data_type', ['decimal', 'integer', 'float'])->default('decimal');
            $table->integer('decimal_places')->default(4); // Presisi untuk penyimpanan data
            $table->string('category'); // e.g., "Migrasi", "Kontaminasi", "Radiologi"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_parameters');
    }
};
