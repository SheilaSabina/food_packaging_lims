<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'client_name',
        'product_type',
        'status',
    ];

    public function testSessions(): HasMany
    {
        return $this->hasMany(TestSession::class);
    }
}
