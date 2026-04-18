<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\TestParameter;
use App\Models\TestResult;
use App\Models\Order;

class TestSession extends Model
{
    protected $guarded = [
        'order_id',
        'technician_id',
        'supervisor_id',
        'equipment_id',
        'equipment_status',
        'equipment_calibrated_at',
        'equipment_calibration_expires_at',
        'equipment_is_calibrated',
        'test_started_at',
        'test_ended_at',
        'test_method',
        'status',
        'rejection_reason',
        'verified_at',
    ];

    protected $casts = [
        'equipment_calibrated_at' => 'datetime',
        'equipment_calibration_expires_at' => 'datetime',
        'equipment_is_calibrated' => 'boolean',
        'test_started_at' => 'datetime',
        'test_ended_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Hubungan ke Order (jika ada)
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Hubungan ke Technician (User)
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Hubungan ke Supervisor (User)
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Hubungan ke TestResults
     */
    public function results(): HasMany
    {
        return $this->hasMany(TestResult::class, 'test_session_id');
    }

    /**
     * Hubungan ke TestEvidences
     */
    public function evidences(): HasMany
    {
        return $this->hasMany(TestEvidence::class, 'test_session_id');
    }

    /**
     * Cek apakah alat siap/dikalibrasi
     */
    public function isEquipmentReady(): bool
    {
        return $this->equipment_is_calibrated &&
            (
                $this->equipment_calibration_expires_at === null ||
                $this->equipment_calibration_expires_at > now()
            );
    }

    /**
     * Cek apakah semua hasil uji sudah input
     */
    public function areAllResultsInputted(): bool
    {
        // Cukup cek apakah ada hasil yang terekam
        return $this->results()->exists();
    }

    /**
     * Cek apakah semua hasil uji sudah diverifikasi
     */
    public function areAllResultsVerified(): bool
    {
        $totalResults = $this->results()->count();
        $verifiedResults = $this->results()
            ->where('data_status', 'Verified')
            ->count();

        return $totalResults > 0 && $totalResults === $verifiedResults;
    }

    /**
     * Dapatkan ringkasan status pengujian
     */
    public function getStatusSummary(): array
        {
            $results = $this->results()->get();
            
            // Samakan key dengan JavaScript di Blade (pass, fail, unknown)
            $pass = $results->where('result_status', 'PASS')->count();
            $fail = $results->where('result_status', 'FAIL')->count();
            $unknown = $results->where('result_status', 'INCONCLUSIVE')->count();

            return [
                'total' => $results->count(),
                'pass' => $pass,
                'fail' => $fail,
                'unknown' => $unknown,
                'overall_status' => $fail > 0 ? 'FAIL' : ($unknown > 0 ? 'INCONCLUSIVE' : 'PASS'),
            ];
        }

    /**
     * Dapatkan tipe produk dari order terkait
     */
    public function getProductType(): ?string
    {
        return $this->order?->product_type;
    }

    /**
     * Ubah status test session
     */
    public function markAsVerified(User $supervisor): bool
    {
        if (!$this->areAllResultsVerified()) {
            return false;
        }

        $this->update([
            'status' => 'Verified',
            'supervisor_id' => $supervisor->id,
            'verified_at' => now(),
        ]);

        return true;
    }

    /**
     * Reject test session dengan alasan
     */
    public function reject(string $reason, User $supervisor): bool
    {
        $this->update([
            'status' => 'Rejected',
            'supervisor_id' => $supervisor->id,
            'rejection_reason' => $reason,
        ]);

        return true;
    }
}
