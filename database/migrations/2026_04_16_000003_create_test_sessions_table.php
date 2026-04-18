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
        Schema::create('test_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('equipment_id')->nullable(); // ID Alat yang digunakan
            $table->enum('equipment_status', ['Alat Siap', 'Kalibrasi Kedaluwarsa', 'In-Progress', 'Draft', 'Verified'])->default('Alat Siap');
            
            // Calibration tracking
            $table->dateTime('equipment_calibrated_at')->nullable();
            $table->dateTime('equipment_calibration_expires_at')->nullable();
            $table->boolean('equipment_is_calibrated')->default(false);
            
            // Testing timing
            $table->dateTime('test_started_at')->nullable();
            $table->dateTime('test_ended_at')->nullable();
            $table->text('test_method')->nullable(); // Metode pengujian yang digunakan
            
            // Status workflow
            $table->enum('status', ['Draft', 'In-Progress', 'Ready for Verification', 'Verified', 'Rejected'])->default('Draft');
            
            // Rejection reason (jika supervisor reject)
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            // Verified at tracking
            $table->dateTime('verified_at')->nullable();

            $table->index(['order_id', 'status']);
            $table->index('equipment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_sessions');
    }
};
