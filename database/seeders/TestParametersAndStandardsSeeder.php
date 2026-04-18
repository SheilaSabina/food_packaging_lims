<?php

namespace Database\Seeders;

use App\Models\TestParameter;
use App\Models\TestStandard;
use Illuminate\Database\Seeder;

class TestParametersAndStandardsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ===== TEST PARAMETERS =====
        
        // 1. Migrasi Total (paling umum dalam pengujian kemasan)
        $migrasiTotal = TestParameter::create([
            'name' => 'Migrasi Total',
            'description' => 'Total migrasi zat ke dalam simulan (overall migration)',
            'unit' => 'mg/dm2',
            'data_type' => 'decimal',
            'decimal_places' => 4,
            'category' => 'Migrasi',
            'is_active' => true,
        ]);

        // 2. Kadar BPA (Bisphenol A)
        $kadarBPA = TestParameter::create([
            'name' => 'Kadar BPA (Bisphenol A)',
            'description' => 'Bisphenol A - Endocrine Disrupting Chemical',
            'unit' => 'µg/L',
            'data_type' => 'decimal',
            'decimal_places' => 6,
            'category' => 'Kontaminasi Kimia',
            'is_active' => true,
        ]);

        // 3. Konsentrasi Radon
        $konsentrasiRadon = TestParameter::create([
            'name' => 'Konsentrasi Radon',
            'description' => 'Radon concentration pada air minum dalam kemasan',
            'unit' => 'Bq/L',
            'data_type' => 'decimal',
            'decimal_places' => 4,
            'category' => 'Radiologi',
            'is_active' => true,
        ]);

        // 4. Kadar Phthalates
        $kadarPhthalates = TestParameter::create([
            'name' => 'Kadar Phthalates',
            'description' => 'Plasticizer - Regulatory concern',
            'unit' => 'mg/kg',
            'data_type' => 'decimal',
            'decimal_places' => 4,
            'category' => 'Kontaminasi Kimia',
            'is_active' => true,
        ]);

        // 5. pH (Acidic/Basic Properties)
        $pH = TestParameter::create([
            'name' => 'pH',
            'description' => 'Derajat keasaman/kebasaan simulan',
            'unit' => '',
            'data_type' => 'decimal',
            'decimal_places' => 2,
            'category' => 'Properti Fisika',
            'is_active' => true,
        ]);

        // ===== TEST STANDARDS (SNI, BPOM, FDA, EFSA) =====

        // Standar untuk Migrasi Total - BPOM untuk semua tipe produk (PerBPOM No. 20/2019)
        $productTypes = ['cair', 'padat', 'kering', 'kemasan'];
        foreach ($productTypes as $productType) {
            TestStandard::create([
                'test_parameter_id' => $migrasiTotal->id,
                'standard_type' => 'BPOM',
                'product_type' => $productType,
                'min_value' => null,
                'max_value' => 10,
                'requirement_description' => "Migrasi total maksimal 10 mg/dm2 untuk produk {$productType}",
                'reference_document' => 'PerBPOM No. 20/2019',
                'effective_date' => '2019-01-01',
                'expired_date' => null,
                'is_active' => true,
            ]);
        }

        // Standar umum untuk Migrasi Total (fallback)
        TestStandard::create([
            'test_parameter_id' => $migrasiTotal->id,
            'standard_type' => 'SNI',
            'min_value' => null,
            'max_value' => 60,
            'requirement_description' => 'Migrasi total tidak boleh melebihi 60 mg/dm2',
            'reference_document' => 'SNI 16371:2019',
            'effective_date' => '2019-06-01',
            'expired_date' => null,
            'is_active' => true,
        ]);

        TestStandard::create([
            'test_parameter_id' => $migrasiTotal->id,
            'standard_type' => 'FDA',
            'min_value' => null,
            'max_value' => 10,
            'requirement_description' => 'Overall migration limit - FDA CFR 165.105',
            'reference_document' => 'FDA CFR Part 165',
            'effective_date' => '2020-01-01',
            'expired_date' => null,
            'is_active' => true,
        ]);

        TestStandard::create([
            'test_parameter_id' => $migrasiTotal->id,
            'standard_type' => 'EU',
            'min_value' => null,
            'max_value' => 10,
            'requirement_description' => 'Overall migration limit - EU Regulation 10/2011',
            'reference_document' => 'EU Regulation 10/2011',
            'effective_date' => '2011-01-01',
            'expired_date' => null,
            'is_active' => true,
        ]);

        // Standar untuk BPA
        TestStandard::create([
            'test_parameter_id' => $kadarBPA->id,
            'standard_type' => 'EFSA',
            'min_value' => 0,
            'max_value' => 0.6,
            'requirement_description' => 'BPA tidak boleh terdeteksi atau minimal > 0.6 µg/L',
            'reference_document' => 'EFSA Safety Assessment BPA',
            'effective_date' => '2020-01-01',
            'expired_date' => null,
            'is_active' => true,
        ]);

        TestStandard::create([
            'test_parameter_id' => $kadarBPA->id,
            'standard_type' => 'FDA',
            'min_value' => 0,
            'max_value' => 2.5,
            'requirement_description' => 'BPA limit - FDA guidelines',
            'reference_document' => 'FDA BPA Assessment',
            'effective_date' => '2021-01-01',
            'expired_date' => null,
            'is_active' => true,
        ]);

        // Standar BPA untuk produk cair (PerBPOM No. 20/2019)
        TestStandard::create([
            'test_parameter_id' => $kadarBPA->id,
            'standard_type' => 'BPOM',
            'product_type' => 'cair',
            'min_value' => null,
            'max_value' => 0.6,
            'requirement_description' => 'Kadar BPA maksimal 0.6 µg/L untuk produk cair',
            'reference_document' => 'PerBPOM No. 20/2019',
            'effective_date' => '2019-01-01',
            'expired_date' => null,
            'is_active' => true,
        ]);

        // Standar BPA untuk produk padat (PerBPOM No. 20/2019)
        TestStandard::create([
            'test_parameter_id' => $kadarBPA->id,
            'standard_type' => 'BPOM',
            'product_type' => 'padat',
            'min_value' => null,
            'max_value' => 0.6,
            'requirement_description' => 'Kadar BPA maksimal 0.6 µg/L untuk produk padat',
            'reference_document' => 'PerBPOM No. 20/2019',
            'effective_date' => '2019-01-01',
            'expired_date' => null,
            'is_active' => true,
        ]);

        // Standar BPA untuk produk kering (PerBPOM No. 20/2019)
        TestStandard::create([
            'test_parameter_id' => $kadarBPA->id,
            'standard_type' => 'BPOM',
            'product_type' => 'kering',
            'min_value' => null,
            'max_value' => 0.6,
            'requirement_description' => 'Kadar BPA maksimal 0.6 µg/L untuk produk kering',
            'reference_document' => 'PerBPOM No. 20/2019',
            'effective_date' => '2019-01-01',
            'expired_date' => null,
            'is_active' => true,
        ]);

        // Standar BPA untuk produk kemasan (PerBPOM No. 20/2019)
        TestStandard::create([
            'test_parameter_id' => $kadarBPA->id,
            'standard_type' => 'BPOM',
            'product_type' => 'kemasan',
            'min_value' => null,
            'max_value' => 0.6,
            'requirement_description' => 'Kadar BPA maksimal 0.6 µg/L untuk produk kemasan',
            'reference_document' => 'PerBPOM No. 20/2019',
            'effective_date' => '2019-01-01',
            'expired_date' => null,
            'is_active' => true,
        ]);

        // Standar untuk Radon
        TestStandard::create([
            'test_parameter_id' => $konsentrasiRadon->id,
            'standard_type' => 'WHO',
            'min_value' => null,
            'max_value' => 100,
            'requirement_description' => 'Radon level in drinking water should not exceed 100 Bq/L',
            'reference_document' => 'WHO Guidelines - Radon in Drinking Water',
            'effective_date' => '2006-01-01',
            'expired_date' => null,
            'is_active' => true,
        ]);

        TestStandard::create([
            'test_parameter_id' => $konsentrasiRadon->id,
            'standard_type' => 'EU',
            'min_value' => null,
            'max_value' => 50,
            'requirement_description' => 'Radon Reference Level - EU Directive',
            'reference_document' => 'EU Directive 2013/51/EURATOM',
            'effective_date' => '2014-11-01',
            'expired_date' => null,
            'is_active' => true,
        ]);

        // Standar Radon untuk produk cair (WHO Guidelines)
        TestStandard::create([
            'test_parameter_id' => $konsentrasiRadon->id,
            'standard_type' => 'WHO',
            'product_type' => 'cair',
            'min_value' => null,
            'max_value' => 100,
            'requirement_description' => 'Konsentrasi Radon maksimal 100 Bq/L untuk produk cair',
            'reference_document' => 'WHO Guidelines for Drinking-water Quality',
            'effective_date' => '2011-01-01',
            'expired_date' => null,
            'is_active' => true,
        ]);

        // Standar Radon untuk produk padat (WHO Guidelines)
        TestStandard::create([
            'test_parameter_id' => $konsentrasiRadon->id,
            'standard_type' => 'WHO',
            'product_type' => 'padat',
            'min_value' => null,
            'max_value' => 100,
            'requirement_description' => 'Konsentrasi Radon maksimal 100 Bq/L untuk produk padat',
            'reference_document' => 'WHO Guidelines for Drinking-water Quality',
            'effective_date' => '2011-01-01',
            'expired_date' => null,
            'is_active' => true,
        ]);

        // Standar Radon untuk produk kering (WHO Guidelines)
        TestStandard::create([
            'test_parameter_id' => $konsentrasiRadon->id,
            'standard_type' => 'WHO',
            'product_type' => 'kering',
            'min_value' => null,
            'max_value' => 100,
            'requirement_description' => 'Konsentrasi Radon maksimal 100 Bq/L untuk produk kering',
            'reference_document' => 'WHO Guidelines for Drinking-water Quality',
            'effective_date' => '2011-01-01',
            'expired_date' => null,
            'is_active' => true,
        ]);

        // Standar Radon untuk produk kemasan (WHO Guidelines)
        TestStandard::create([
            'test_parameter_id' => $konsentrasiRadon->id,
            'standard_type' => 'WHO',
            'product_type' => 'kemasan',
            'min_value' => null,
            'max_value' => 100,
            'requirement_description' => 'Konsentrasi Radon maksimal 100 Bq/L untuk produk kemasan',
            'reference_document' => 'WHO Guidelines for Drinking-water Quality',
            'effective_date' => '2011-01-01',
            'expired_date' => null,
            'is_active' => true,
        ]);

        // Standar untuk Phthalates
        TestStandard::create([
            'test_parameter_id' => $kadarPhthalates->id,
            'standard_type' => 'EU',
            'min_value' => null,
            'max_value' => 0.1,
            'requirement_description' => 'Phthalates concentration limit',
            'reference_document' => 'EU Regulation 10/2011 Annex II',
            'effective_date' => '2011-01-01',
            'expired_date' => null,
            'is_active' => true,
        ]);

        // pH Standards (informative, tidak ada requirement strict)
        TestStandard::create([
            'test_parameter_id' => $pH->id,
            'standard_type' => 'SNI',
            'min_value' => 6.5,
            'max_value' => 8.5,
            'requirement_description' => 'pH air minum dalam kemasan',
            'reference_document' => 'SNI 01-3553-2006',
            'effective_date' => '2006-01-01',
            'expired_date' => null,
            'is_active' => true,
        ]);

        $this->command->info('✓ Test Parameters and Standards seeded successfully!');
    }
}
