<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountRule extends Model
{
    protected $fillable = [
        'name',
        'type',
        'min_nights',
        'within_days',
        'discount_percentage',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_nights' => 'integer',
            'within_days' => 'integer',
            'discount_percentage' => 'decimal:2',
            'priority' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
