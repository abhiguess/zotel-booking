<?php

namespace App\Services;

use App\Models\DiscountRule;
use Illuminate\Support\Collection;

class DiscountService
{
    /**
     * Resolve the best applicable discount for the given search parameters.
     *
     * @return array{applied: bool, type: string|null, name: string|null, percentage: float}
     */
    public function resolveBestDiscount(int $nights, int $daysUntilCheckin): array
    {
        $noDiscount = [
            'applied' => false,
            'type' => null,
            'name' => null,
            'percentage' => 0.0,
        ];

        $activeRules = DiscountRule::query()
            ->where('is_active', true)
            ->get();

        $bestLongStay = $this->findBestLongStay($activeRules, $nights);
        $bestLastMinute = $this->findBestLastMinute($activeRules, $daysUntilCheckin);

        if (! $bestLongStay && ! $bestLastMinute) {
            return $noDiscount;
        }

        if ($bestLongStay && ! $bestLastMinute) {
            return $this->formatDiscount($bestLongStay);
        }

        if (! $bestLongStay && $bestLastMinute) {
            return $this->formatDiscount($bestLastMinute);
        }

        // Both exist — pick higher percentage, prefer long_stay on tie
        if ((float) $bestLongStay->discount_percentage >= (float) $bestLastMinute->discount_percentage) {
            return $this->formatDiscount($bestLongStay);
        }

        return $this->formatDiscount($bestLastMinute);
    }

    /**
     * Get all active discount rules grouped by type.
     *
     * @return array<string, Collection<int, DiscountRule>>
     */
    public function getActiveRulesGroupedByType(): array
    {
        $rules = DiscountRule::query()
            ->where('is_active', true)
            ->orderBy('priority')
            ->get();

        return [
            'long_stay' => $rules->where('type', 'long_stay')->values(),
            'last_minute' => $rules->where('type', 'last_minute')->values(),
        ];
    }

    private function findBestLongStay(Collection $rules, int $nights): ?DiscountRule
    {
        return $rules
            ->where('type', 'long_stay')
            ->where('min_nights', '<=', $nights)
            ->sortByDesc('discount_percentage')
            ->first();
    }

    private function findBestLastMinute(Collection $rules, int $daysUntilCheckin): ?DiscountRule
    {
        return $rules
            ->where('type', 'last_minute')
            ->where('within_days', '>=', $daysUntilCheckin)
            ->sortByDesc('discount_percentage')
            ->first();
    }

    /**
     * @return array{applied: bool, type: string, name: string, percentage: float}
     */
    private function formatDiscount(DiscountRule $rule): array
    {
        return [
            'applied' => true,
            'type' => $rule->type,
            'name' => $rule->name,
            'percentage' => (float) $rule->discount_percentage,
        ];
    }
}
