<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestResult extends Model
{
    protected $fillable = [
        'test_session_id',
        'test_parameter_id',
        'measured_value',
        'unit',
        'applied_standard_id',
        'standard_min_value',
        'standard_max_value',
        'result_status',
        'deviation_percentage',
        'data_status',
        'notes',
        'calculation_details',
    ];

    protected $casts = [
        'measured_value' => 'decimal:8',
        'standard_min_value' => 'decimal:8',
        'standard_max_value' => 'decimal:8',
        'deviation_percentage' => 'decimal:4',
        'calculation_details' => 'array',
    ];

    /**
     * Hubungan ke TestSession
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(TestSession::class, 'test_session_id');
    }

    /**
     * Hubungan ke TestParameter
     */
    public function parameter(): BelongsTo
    {
        return $this->belongsTo(TestParameter::class, 'test_parameter_id');
    }

    /**
     * Hubungan ke TestStandard yang diaplikasikan
     */
    public function appliedStandard(): BelongsTo
    {
        return $this->belongsTo(TestStandard::class, 'applied_standard_id');
    }

    /**
     * Hubungan ke TestEvidences
     */
    public function evidences(): HasMany
    {
        return $this->hasMany(TestEvidence::class, 'test_result_id');
    }

    /**
     * Dapatkan status dengan warna/label yang user-friendly
     */
    public function getStatusBadge(): string
    {
        return match ($this->result_status) {
            'PASS' => '✅ LULUS',
            'FAIL' => '❌ GAGAL',
            'INCONCLUSIVE' => '⚠️ TIDAK PASTI',
            null => '⏳ BELUM DINILAI',
            default => 'TIDAK DIKETAHUI',
        };
    }

    /**
     * Ubah status data (workflow)
     */
    public function updateDataStatus(string $newStatus): bool
    {
        $validStatuses = ['Alat Siap', 'In-Progress', 'Draft', 'Verified'];
        
        if (!in_array($newStatus, $validStatuses)) {
            return false;
        }

        $this->update(['data_status' => $newStatus]);
        return true;
    }

    /**
     * Dapatkan deskripsi hasil untuk laporan
     */
    public function getResultDescription(): string
    {
        if ($this->result_status === null) {
            return "Belum ada standar yang sesuai untuk perbandingan otomatis.";
        }

        $parameterName = $this->parameter->name;
        $measuredValue = $this->measured_value;
        $unit = $this->unit;

        if ($this->result_status === 'PASS') {
            return "{$parameterName}: {$measuredValue} {$unit} ✅ Memenuhi standar";
        }

        if ($this->result_status === 'FAIL') {
            $details = [];
            if ($this->standard_min_value !== null) {
                $details[] = "Min: {$this->standard_min_value}";
            }
            if ($this->standard_max_value !== null) {
                $details[] = "Max: {$this->standard_max_value}";
            }
            $rangeText = implode(', ', $details);
            return "{$parameterName}: {$measuredValue} {$unit} ❌ Melebihi standar ({$rangeText})";
        }

        return "{$parameterName}: {$measuredValue} {$unit} ⚠️ Tidak dapat ditentukan";
    }
}
