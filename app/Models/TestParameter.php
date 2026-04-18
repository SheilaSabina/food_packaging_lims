<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestParameter extends Model
{
    protected $fillable = [
        'name',
        'description',
        'unit',
        'data_type',
        'decimal_places',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'decimal_places' => 'integer',
    ];

    /**
     * Hubungan ke TestStandard
     */
    public function standards(): HasMany
    {
        return $this->hasMany(TestStandard::class, 'test_parameter_id');
    }

    /**
     * Hubungan ke TestResult
     */
    public function results(): HasMany
    {
        return $this->hasMany(TestResult::class, 'test_parameter_id');
    }

    /**
     * Dapatkan standar yang aktif untuk parameter ini
     */
    public function activeStandards(?string $productType = null)
    {
        $query = $this->standards()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('effective_date')
                    ->orWhere('effective_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expired_date')
                    ->orWhere('expired_date', '>=', now());
            });

        // Jika product_type diberikan, prioritaskan standar yang sesuai dengan product_type
        if ($productType) {
            $query->where(function ($subQuery) use ($productType) {
                $subQuery->where('product_type', $productType)
                         ->orWhereNull('product_type'); // Fallback ke standar umum jika tidak ada yang spesifik
            });
        }

        return $query;
    }

    /**
     * Format nilai dengan presisi yang tepat
     */
    public function formatValue($value): string
    {
        $decimalPlaces = $this->decimal_places ?? 4;
        return number_format((float)$value, $decimalPlaces, '.', '');
    }

    /**
     * Validasi apakah nilai numerik valid
     */
    public function isValidNumericValue($value): bool
    {
        if (!is_numeric($value)) {
            return false;
        }

        // Cek decimal places
        $parts = explode('.', (string)$value);
        if (count($parts) === 2 && strlen($parts[1]) > $this->decimal_places) {
            return false;
        }

        return true;
    }
}
