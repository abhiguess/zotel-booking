<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RatePlanDailyRate extends Model
{
    protected $fillable = [
        'rate_plan_id',
        'date',
        'occupancy',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'occupancy' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo(RatePlan::class);
    }

    public function scopeForDateRange(Builder $query, string $checkIn, string $checkOut): Builder
    {
        return $query->where('date', '>=', $checkIn)->where('date', '<', $checkOut);
    }

    public function scopeForOccupancy(Builder $query, int $occupancy): Builder
    {
        return $query->where('occupancy', $occupancy);
    }
}
