<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestEvidence extends Model
{
    protected $table = 'test_evidences';
    
    protected $fillable = [
        'test_result_id',
        'test_session_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'evidence_type',
        'description',
        'uploaded_by',
        'uploaded_at',
        'metadata',
        'is_verified',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'is_verified' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Hubungan ke TestResult
     */
    public function result(): BelongsTo
    {
        return $this->belongsTo(TestResult::class, 'test_result_id');
    }

    /**
     * Hubungan ke TestSession
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(TestSession::class, 'test_session_id');
    }

    /**
     * Validasi tipe file
     */
    public static function isValidFileType(string $mimeType): bool
    {
        $allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
        ];

        return in_array($mimeType, $allowedTypes);
    }

    /**
     * Validasi ukuran file (max 10MB)
     */
    public static function isValidFileSize(int $fileSize): bool
    {
        return $fileSize <= 10 * 1024 * 1024; // 10MB
    }

    /**
     * Format ukuran file untuk display
     */
    public function getFormattedFileSize(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;

        foreach ($units as $unit) {
            if ($size < 1024) {
                return round($size, 2) . ' ' . $unit;
            }
            $size = $size / 1024;
        }

        return round($size, 2) . ' GB';
    }

    /**
     * Dapatkan tipe bukti dengan label
     */
    public function getEvidenceTypeLabel(): string
    {
        return match ($this->evidence_type) {
            'Test Photo' => 'Foto Pengujian',
            'Equipment Status' => 'Status Alat',
            'Calibration Certificate' => 'Sertifikat Kalibrasi',
            'Other' => 'Lainnya',
            default => $this->evidence_type,
        };
    }
}
