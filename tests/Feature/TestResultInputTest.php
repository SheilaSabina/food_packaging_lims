<?php

namespace Tests\Feature;

use App\Models\TestParameter;
use App\Models\TestSession;
use App\Models\TestStandard;
use App\Models\User;
use App\Services\TestResultComparisonService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestResultInputTest extends TestCase
{
    use RefreshDatabase;

    private TestResultComparisonService $comparisonService;
    private User $technician;
    private TestSession $session;
    private TestParameter $migrasiTotal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->comparisonService = app(TestResultComparisonService::class);
        
        // Create test users
        $this->technician = User::factory()
            ->create(['name' => 'Budi Santoso', 'role' => 'technician']);

        // Create test parameter
        $this->migrasiTotal = TestParameter::create([
            'name' => 'Migrasi Total',
            'description' => 'Total migrasi zat ke dalam simulan',
            'unit' => 'mg/dm2',
            'data_type' => 'decimal',
            'decimal_places' => 4,
            'category' => 'Migrasi',
            'is_active' => true,
        ]);

        // Create test session
        $this->session = TestSession::create([
            'technician_id' => $this->technician->id,
            'equipment_id' => 'SPEC-001',
            'equipment_is_calibrated' => true,
            'equipment_calibration_expires_at' => now()->addMonths(1),
            'status' => 'Draft',
        ]);
    }

    /**
     * Test: Input numerik dengan nilai valid
     * AC: Hanya terima angka
     */
    public function test_input_numeric_with_valid_value(): void
    {
        $result = $this->comparisonService->inputNumericData(
            $this->session,
            $this->migrasiTotal,
            25.5,
            'Test notes'
        );

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['data']);
        $this->assertEquals('25.50000000', $result['data']->measured_value);
        $this->assertStringContainsString('✓ Nilai numerik valid', implode(' ', $result['messages']));
    }

    /**
     * Test: Input numerik dengan presisi melebihi batas
     * AC: Validasi presisi desimal
     */
    public function test_input_numeric_with_excessive_precision(): void
    {
        $result = $this->comparisonService->inputNumericData(
            $this->session,
            $this->migrasiTotal,
            25.123456789,
            'Too many decimals'
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('tidak valid', implode(' ', $result['messages']));
    }

    /**
     * Test: Input numerik dengan perbandingan otomatis - PASS
     * AC: Otomatis bandingkan dengan threshold
     */
    public function test_input_numeric_with_automatic_comparison_pass(): void
    {
        // Create standards
        TestStandard::create([
            'test_parameter_id' => $this->migrasiTotal->id,
            'standard_type' => 'SNI',
            'max_value' => 60,
            'reference_document' => 'SNI 16371:2019',
            'is_active' => true,
        ]);

        TestStandard::create([
            'test_parameter_id' => $this->migrasiTotal->id,
            'standard_type' => 'FDA',
            'max_value' => 10,
            'reference_document' => 'FDA CFR Part 165',
            'is_active' => true,
        ]);

        $result = $this->comparisonService->inputNumericData(
            $this->session,
            $this->migrasiTotal,
            5.5
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('PASS', $result['data']->result_status);
        $this->assertStringContainsString('memenuhi standar', implode(' ', $result['messages']));
    }

    /**
     * Test: Input numerik dengan perbandingan otomatis - FAIL
     * AC: Indikator GAGAL real-time
     */
    public function test_input_numeric_with_automatic_comparison_fail(): void
    {
        // Create standards
        TestStandard::create([
            'test_parameter_id' => $this->migrasiTotal->id,
            'standard_type' => 'FDA',
            'max_value' => 10,
            'reference_document' => 'FDA CFR Part 165',
            'is_active' => true,
        ]);

        $result = $this->comparisonService->inputNumericData(
            $this->session,
            $this->migrasiTotal,
            45.5
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('FAIL', $result['data']->result_status);
        $this->assertStringContainsString('melebihi', implode(' ', $result['messages']));
    }

    /**
     * Test: Input numerik tanpa standar - INCONCLUSIVE
     * AC: Eskalasi ke admin jika threshold belum diatur
     */
    public function test_input_numeric_without_standards_returns_inconclusive(): void
    {
        $result = $this->comparisonService->inputNumericData(
            $this->session,
            $this->migrasiTotal,
            25.5
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('INCONCLUSIVE', $result['data']->result_status);
        $this->assertStringContainsString('Tidak ada standar aktif', implode(' ', $result['messages']));
    }

    /**
     * Test: Hitung deviasi persentase
     * AC: Deviation percentage dihitung otomatis
     */
    public function test_deviation_percentage_calculation(): void
    {
        TestStandard::create([
            'test_parameter_id' => $this->migrasiTotal->id,
            'standard_type' => 'FDA',
            'max_value' => 10,
            'reference_document' => 'FDA CFR Part 165',
            'is_active' => true,
        ]);

        $result = $this->comparisonService->inputNumericData(
            $this->session,
            $this->migrasiTotal,
            15.5
        );

        $this->assertTrue($result['success']);
        // Deviation: ((15.5 - 10) / 10) * 100 = 55%
        $this->assertEquals('55.0000', $result['data']->deviation_percentage);
    }

    /**
     * Test: Status data berubah ke Draft setelah input
     * AC: data_status = 'Draft'
     */
    public function test_data_status_set_to_draft(): void
    {
        $result = $this->comparisonService->inputNumericData(
            $this->session,
            $this->migrasiTotal,
            25.5
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('Draft', $result['data']->data_status);
    }

    /**
     * Test: Test session status berubah ke In-Progress
     * AC: Session status otomatis update
     */
    public function test_session_status_updated_to_in_progress(): void
    {
        $this->assertEquals('Draft', $this->session->status);

        $this->comparisonService->inputNumericData(
            $this->session,
            $this->migrasiTotal,
            25.5
        );

        $this->session->refresh();
        $this->assertEquals('In-Progress', $this->session->status);
    }

    /**
     * Test: Calculation details disimpan untuk audit trail
     * AC: JSON calculation_details tersimpan
     */
    public function test_calculation_details_stored_for_audit(): void
    {
        TestStandard::create([
            'test_parameter_id' => $this->migrasiTotal->id,
            'standard_type' => 'SNI',
            'max_value' => 60,
            'reference_document' => 'SNI 16371:2019',
            'is_active' => true,
        ]);

        $result = $this->comparisonService->inputNumericData(
            $this->session,
            $this->migrasiTotal,
            25.5
        );

        $this->assertTrue($result['success']);
        $this->assertIsArray($result['data']->calculation_details);
        $this->assertArrayHasKey('measured_value', $result['data']->calculation_details);
        $this->assertArrayHasKey('comparison_results', $result['data']->calculation_details);
        $this->assertArrayHasKey('timestamp', $result['data']->calculation_details);
    }

    /**
     * Test: Multiple standards support - FAIL jika ada 1 yang fail
     * AC: Sistem support SNI, BPOM, FDA, EFSA
     */
    public function test_multiple_standards_fail_if_one_fails(): void
    {
        // Create multiple standards
        TestStandard::create([
            'test_parameter_id' => $this->migrasiTotal->id,
            'standard_type' => 'SNI',
            'max_value' => 60,
            'reference_document' => 'SNI 16371:2019',
            'is_active' => true,
        ]);

        TestStandard::create([
            'test_parameter_id' => $this->migrasiTotal->id,
            'standard_type' => 'FDA',
            'max_value' => 10,
            'reference_document' => 'FDA CFR Part 165',
            'is_active' => true,
        ]);

        TestStandard::create([
            'test_parameter_id' => $this->migrasiTotal->id,
            'standard_type' => 'EU',
            'max_value' => 10,
            'reference_document' => 'EU Regulation 10/2011',
            'is_active' => true,
        ]);

        $result = $this->comparisonService->inputNumericData(
            $this->session,
            $this->migrasiTotal,
            45.5
        );

        $this->assertTrue($result['success']);
        // Should FAIL because FDA and EU limits are 10
        $this->assertEquals('FAIL', $result['data']->result_status);
    }
}
