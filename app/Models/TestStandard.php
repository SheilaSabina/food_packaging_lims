<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestStandard extends Model
{
    protected $fillable = [
        'test_parameter_id',
        'standard_type',
        'product_type',
        'min_value',
        'max_value',
        'requirement_description',
        'reference_document',
        'effective_date',
        'expired_date',
        'is_active',
    ];

    protected $casts = [
        'min_value' => 'decimal:8',
        'max_value' => 'decimal:8',
        'effective_date' => 'date',
        'expired_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Hubungan ke TestParameter
     */
    public function parameter(): BelongsTo
    {
        return $this->belongsTo(TestParameter::class, 'test_parameter_id');
    }

    /**
     * Cek apakah standar aktif saat ini
     */
    public function isCurrentlyActive(): bool
    {
        $now = now()->toDateString();

        if (!$this->is_active) {
            return false;
        }

        if ($this->effective_date && $this->effective_date > $now) {
            return false;
        }

        if ($this->expired_date && $this->expired_date < $now) {
            return false;
        }

        return true;
    }

    /**
     * Dapatkan tipe standar dengan label
     */
    public function getStandardTypeLabel(): string
    {
        return match ($this->standard_type) {
            'SNI' => 'Standar Nasional Indonesia',
            'BPOM' => 'Badan Pengawas Obat dan Makanan',
            'FDA' => 'Food and Drug Administration (USA)',
            'EFSA' => 'European Food Safety Authority',
            'EU' => 'European Union',
            default => $this->standard_type,
        };
    }

    /**
     * Cek apakah nilai memenuhi standar
     * @param float $value Nilai yang diuji
     * @return array ['is_pass' => bool, 'reason' => string]
     */
    public function checkCompliance(float $value): array
    {
        // Jika standar tidak aktif, tidak bisa digunakan untuk penilaian
        if (!$this->isCurrentlyActive()) {
            return [
                'is_pass' => false,
                'reason' => "Standar {$this->standard_type} tidak aktif ({$this->reference_document})",
            ];
        }

        $min = (float)$this->min_value;
        $max = (float)$this->max_value;

        // Cek nilai minimum
        if ($this->min_value !== null && $value < $min) {
            return [
                'is_pass' => false,
                'reason' => "Nilai {$value} lebih rendah dari minimum yang diizinkan ({$min})",
            ];
        }

        // Cek nilai maksimum
        if ($this->max_value !== null && $value > $max) {
            return [
                'is_pass' => false,
                'reason' => "Nilai {$value} melebihi maksimum yang diizinkan ({$max})",
            ];
        }

        return [
            'is_pass' => true,
            'reason' => "Nilai {$value} memenuhi standar {$this->standard_type}",
        ];
    }
}
