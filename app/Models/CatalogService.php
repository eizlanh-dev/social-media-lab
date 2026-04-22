<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogService extends Model
{
    protected $fillable = [
        'external_service_id',
        'name',
        'category',
        'type',
        'min',
        'max',
        'raw_rate',
        'sell_rate',
        'is_active',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'meta' => 'array',
            'raw_rate' => 'decimal:6',
            'sell_rate' => 'decimal:6',
        ];
    }
}
