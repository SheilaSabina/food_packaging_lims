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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('client_name');
            
            // Tambahkan ini agar US-2.5 (Otomasi) punya konteks
            $table->string('product_type')->default('kering');
            
            $table->enum('status', [
                'Menunggu Pengiriman', 
                'Sampel Dalam Perjalanan', 
                'Sampel Diterima', 
                'Selesai'
            ])->default('Menunggu Pengiriman');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
