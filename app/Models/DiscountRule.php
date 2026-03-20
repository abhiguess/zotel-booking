<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscountRule extends Model
{
    protected $fillable = [
        'rate_plan_id',
        'name',
        'type',
        'min_nights',
        'within_days',
        'min_days_before',
        'discount_percentage',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_nights' => 'integer',
            'within_days' => 'integer',
            'min_days_before' => 'integer',
            'discount_percentage' => 'decimal:2',
            'priority' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo(RatePlan::class);
    }

    public function scopeForRatePlan(Builder $query, int $ratePlanId): Builder
    {
        return $query->where('rate_plan_id', $ratePlanId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isEarlyBirdQualified(int $daysUntilCheckin): bool
    {
        return $this->type === 'early_bird'
            && $this->min_days_before !== null
            && $daysUntilCheckin >= $this->min_days_before;
    }
}
