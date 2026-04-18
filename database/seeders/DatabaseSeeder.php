<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\TestSession;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(TestParametersAndStandardsSeeder::class);

        $technician = User::factory()->create([
            'name' => 'Technician Tester',
            'email' => 'technician@example.com',
            'role' => 'technician',
            'password' => bcrypt('password'),
        ]);

        $order = Order::create([
            'order_number' => 'ORD-2026-0001',
            'client_name' => 'PT Indofood Sukses Makmur Tbk',
            'product_type' => 'cair',
            'status' => 'Sampel Diterima',
        ]);

        TestSession::create([
            'order_id' => $order->id,
            'technician_id' => $technician->id,
            'equipment_id' => 'EQ-001',
            'equipment_status' => 'Alat Siap',
            'equipment_calibrated_at' => now()->subDays(1),
            'equipment_calibration_expires_at' => now()->addDays(30),
            'equipment_is_calibrated' => true,
            'test_method' => 'Uji migrasi dan kontaminasi sesuai standar.',
            'status' => 'Draft',
        ]);

        $dummyOrders = [
            ['client_name' => 'PT Unilever Indonesia Tbk', 'product_type' => 'cair', 'equipment_id' => 'EQ-002', 'status' => 'Draft'],
            ['client_name' => 'PT Nestle Indonesia', 'product_type' => 'padat', 'equipment_id' => 'EQ-003', 'status' => 'Draft'],
            ['client_name' => 'PT Mayora Indah Tbk', 'product_type' => 'kemasan', 'equipment_id' => 'EQ-004', 'status' => 'Draft'],
            ['client_name' => 'PT Wings Surya', 'product_type' => 'padat', 'equipment_id' => 'EQ-005', 'status' => 'In-Progress'],
            ['client_name' => 'PT ABC Food', 'product_type' => 'kemasan', 'equipment_id' => 'EQ-006', 'status' => 'In-Progress'],
        ];

        foreach ($dummyOrders as $index => $dummy) {
            $newOrder = Order::create([
                'order_number' => sprintf('ORD-2026-00%02d', $index + 2),
                'client_name' => $dummy['client_name'],
                'product_type' => $dummy['product_type'],
                'status' => 'Sampel Diterima',
            ]);

            TestSession::create([
                'order_id' => $newOrder->id,
                'technician_id' => $technician->id,
                'equipment_id' => $dummy['equipment_id'],
                'equipment_status' => 'Alat Siap',
                'equipment_calibrated_at' => now()->subDays(2 + $index),
                'equipment_calibration_expires_at' => now()->addDays(20 + $index * 2),
                'equipment_is_calibrated' => true,
                'test_method' => 'Uji kualitas produk sesuai standar LIMS.',
                'status' => $dummy['status'],
            ]);
        }
    }
}
