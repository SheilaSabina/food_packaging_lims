<?php

namespace App\Services;

use App\Models\TestParameter;
use App\Models\TestResult;
use App\Models\TestSession;
use App\Models\TestStandard;
use Illuminate\Support\Facades\DB;

class TestResultComparisonService
{
    /**
     * Input data numerik dengan validasi dan perbandingan otomatis (US-2.4 & US-2.5)
     * 
     * @param TestSession $session
     * @param TestParameter $parameter
     * @param float $measuredValue Nilai hasil pengukuran
     * @param string|null $notes Catatan tambahan
     * @return array ['success' => bool, 'data' => TestResult|null, 'messages' => array]
     */

    public function inputNumericData(TestSession $session, TestParameter $parameter, float $measuredValue, ?string $notes = null): array 
    {
        $messages = [];

        // STEP 1: Validasi
        if (!$parameter->isValidNumericValue($measuredValue)) {
            return [
                'success' => false,
                'data' => null,
                'messages' => ["Nilai numerik tidak valid. Presisi maksimal {$parameter->decimal_places} desimal."],
            ];
        }

        $formattedValue = $parameter->formatValue($measuredValue);
        $messages[] = "✓ Nilai numerik valid: {$formattedValue} {$parameter->unit}";

        $productType = $session->order->product_type ?? null;
        $applicableStandards = TestStandard::query()
            ->where('test_parameter_id', $parameter->id)
            ->where('is_active', true)
            ->where(function ($query) use ($productType) {
                if ($productType) {
                    // Cari yang tipenya COCOK atau yang bersifat UMUM (null)
                    $query->where('product_type', $productType)
                        ->orWhereNull('product_type');
                } else {
                    $query->whereNull('product_type');
                }
            })
            ->where(function ($query) {
                $query->whereNull('effective_date')
                      ->orWhere('effective_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expired_date')
                      ->orWhere('expired_date', '>=', now());
            })
            ->get();

        if ($applicableStandards->isEmpty()) {
            $messages[] = "Tidak ada standar aktif untuk perbandingan otomatis";
            return $this->saveTestResult(
                $session, $parameter, $formattedValue, null, null, null, 
                'INCONCLUSIVE', $notes, [], $messages
            );
        }

        // STEP 3: Komparasi
        $comparisonResults = $this->compareWithAllStandards($formattedValue, $parameter, $applicableStandards);
        $messages = array_merge($messages, $comparisonResults['messages']);

        // STEP 4: Simpan
        return $this->saveTestResult(
            $session, $parameter, $formattedValue, 
            $comparisonResults['selected_standard'],
            $comparisonResults['selected_standard']?->min_value,
            $comparisonResults['selected_standard']?->max_value,
            $comparisonResults['overall_status'],
            $notes,
            $comparisonResults['calculation_details'],
            $messages // Kirim di argumen ke-10
        );
    }

    /**
     * Bandingkan nilai dengan semua standar yang berlaku
     */
    private function compareWithAllStandards(
        float $measuredValue,
        TestParameter $parameter,
        $standards
    ): array {
        $results = [];
        $selectedStandard = null;
        $overallStatus = 'PASS'; // Default PASS jika semua standar terpenuhi
        $messages = [];

        foreach ($standards as $standard) {
            $complianceCheck = $standard->checkCompliance($measuredValue);

            $results[] = [
                'standard' => $standard,
                'is_pass' => $complianceCheck['is_pass'],
                'reason' => $complianceCheck['reason'],
            ];

            // Jika ada standar yang FAIL, ubah status menjadi FAIL
            if (!$complianceCheck['is_pass']) {
                $overallStatus = 'FAIL';
                $messages[] = "✗ {$complianceCheck['reason']}";
                
                // Pilih standar pertama yang FAIL untuk detail laporan
                if (!$selectedStandard) {
                    $selectedStandard = $standard;
                }
            } else {
                $messages[] = "✓ {$complianceCheck['reason']}";
                // Pilih standar pertama yang PASS jika belum ada yang fail
                if ($selectedStandard === null) {
                    $selectedStandard = $standard;
                }
            }
        }

        // Hitung deviasi persentase jika ada standar terpilih
        $deviationPercentage = null;
        if ($selectedStandard) {
            $deviationPercentage = $this->calculateDeviation(
                $measuredValue,
                $selectedStandard
            );
        }

        return [
            'overall_status' => $overallStatus,
            'selected_standard' => $selectedStandard,
            'messages' => $messages,
            'deviation_percentage' => $deviationPercentage,
            'calculation_details' => [
                'measured_value' => $measuredValue,
                'parameter_id' => $parameter->id,
                'standards_checked' => count($results),
                'comparison_results' => $results,
                'timestamp' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Hitung persentase deviasi dari standar
     */
    private function calculateDeviation(float $measuredValue, TestStandard $standard): ?float
    {
        if ($standard->max_value === null && $standard->min_value === null) {
            return null;
        }

        $baseValue = $standard->max_value ?? $standard->min_value;
        if ($baseValue == 0) {
            return null;
        }

        return (($measuredValue - (float)$baseValue) / (float)$baseValue) * 100;
    }

    /**
     * Simpan hasil pengujian ke database dengan perhitungan deviasi final
     */
    private function saveTestResult($session, $parameter, $measuredValue, $standard, $minValue, $maxValue, $resultStatus, $notes, $calculationDetails, array $messages = []): array 
    {
        try {
            // Kita tambahkan $messages ke dalam 'use' agar pesan tersinkronisasi
            return DB::transaction(function () use ($session, $parameter, $measuredValue, $standard, $minValue, $maxValue, $resultStatus, $notes, $calculationDetails, $messages) {
                
                // --- BAGIAN KRUSIAL: Hitung deviasi sebelum disimpan ---
                $deviation = null;
                if ($standard) {
                    $deviation = $this->calculateDeviation($measuredValue, $standard);
                }

                $existingResult = TestResult::where('test_session_id', $session->id)
                    ->where('test_parameter_id', $parameter->id)
                    ->first();

                $resultData = [
                    'measured_value' => $measuredValue,
                    'unit' => $parameter->unit,
                    'applied_standard_id' => $standard?->id,
                    'standard_min_value' => $minValue,
                    'standard_max_value' => $maxValue,
                    'result_status' => $resultStatus,
                    'deviation_percentage' => $deviation, // Simpan hasil perhitungan ke sini
                    'data_status' => 'Draft',
                    'notes' => $notes,
                    'calculation_details' => $calculationDetails,
                ];

                if ($existingResult) {
                    $existingResult->update($resultData);
                    // Gunakan fresh() agar data numerik & deviasi diambil ulang dari DB
                    $testResult = $existingResult->fresh(); 
                    $messages[] = "✓ Data hasil uji diperbarui";
                } else {
                    $testResult = TestResult::create([
                        'test_session_id' => $session->id, 
                        'test_parameter_id' => $parameter->id, 
                        ...$resultData
                    ]);
                    $testResult = $testResult->fresh(); // Ambil data segar setelah create
                    $messages[] = "✓ Data hasil uji tersimpan";
                }

                if ($session->status === 'Draft') {
                    $session->update([
                        'status' => 'In-Progress', 
                        'test_started_at' => $session->test_started_at ?? now()
                    ]);
                    $messages[] = "✓ Status sesi pengujian: In-Progress";
                }

                return [
                    'success' => true,
                    'data' => $testResult,
                    'messages' => $messages,
                ];
            });
        } catch (\Exception $e) {
            return [
                'success' => false, 
                'data' => null, 
                'messages' => ["Gagal menyimpan data: " . $e->getMessage()]
            ];
        }
    }
    /**
     * Verifikasi semua hasil uji dalam sesi (Supervisor approval)
     */
    public function verifySessionResults(TestSession $session): array
    {
        $results = $session->results;

        if ($results->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Tidak ada hasil uji untuk diverifikasi',
            ];
        }

        $failedResults = $results->filter(fn($r) => $r->result_status === 'FAIL');

        try {
            return DB::transaction(function () use ($session, $results, $failedResults) {
                // Update semua hasil ke status Verified
                foreach ($results as $result) {
                    $result->update(['data_status' => 'Verified']);
                }

                // Update session status
                $session->update([
                    'status' => 'Ready for Verification',
                    'test_ended_at' => now(),
                ]);

                $summary = [
                    'total_results' => $results->count(),
                    'passed_results' => $results->filter(fn($r) => $r->result_status === 'PASS')->count(),
                    'failed_results' => $failedResults->count(),
                    'overall_status' => $failedResults->isEmpty() ? 'PASS' : 'FAIL',
                ];

                return [
                    'success' => true,
                    'message' => 'Semua hasil uji diverifikasi',
                    'summary' => $summary,
                ];
            });
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => "Gagal memverifikasi: {$e->getMessage()}",
            ];
        }
    }

    /**
     * Validasi dan lock hasil setelah supervisor approval
     */
    public function lockResults(TestSession $session): bool
    {
        try {
            return DB::transaction(function () use ($session) {
                $session->results->each(function (TestResult $result) {
                    $result->update(['data_status' => 'Verified']);
                });

                $session->update([
                    'status' => 'Verified',
                    'verified_at' => now(),
                ]);

                return true;
            });
        } catch (\Exception $e) {
            return false;
        }
    }
}
