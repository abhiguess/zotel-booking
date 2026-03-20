<?php

namespace App\Services;

use App\Models\DiscountRule;
use Illuminate\Support\Collection;

class DiscountService
{
    /**
     * Resolve the best applicable discount for a specific rate plan.
     *
     * @return array{applied: bool, name: string|null, type: string|null, percentage: float, amount: float}
     */
    public function resolveBestDiscountForRatePlan(
        int $ratePlanId,
        int $nights,
        int $daysUntilCheckin,
        float $baseTotal,
    ): array {
        $noDiscount = [
            'applied' => false,
            'name' => null,
            'type' => null,
            'percentage' => 0,
            'amount' => 0,
        ];

        $activeRules = DiscountRule::query()
            ->where('rate_plan_id', $ratePlanId)
            ->where('is_active', true)
            ->get();

        if ($activeRules->isEmpty()) {
            return $noDiscount;
        }

        $bestRule = $this->findBestQualifyingRule($activeRules, $nights, $daysUntilCheckin);

        if (! $bestRule) {
            return $noDiscount;
        }

        $percentage = (float) $bestRule->discount_percentage;
        $amount = round($baseTotal * $percentage / 100, 2);

        return [
            'applied' => true,
            'name' => $bestRule->name,
            'type' => $bestRule->type,
            'percentage' => $percentage,
            'amount' => $amount,
        ];
    }

    /**
     * Get all active discount rules with their rate plan info for display.
     *
     * @return Collection<int, DiscountRule>
     */
    public function getActiveRulesWithRatePlans(): Collection
    {
        return DiscountRule::query()
            ->where('is_active', true)
            ->whereNotNull('rate_plan_id')
            ->with('ratePlan.roomType')
            ->orderBy('priority')
            ->get();
    }

    private function findBestQualifyingRule(Collection $rules, int $nights, int $daysUntilCheckin): ?DiscountRule
    {
        $qualifying = $rules->filter(function (DiscountRule $rule) use ($nights, $daysUntilCheckin) {
            return match ($rule->type) {
                'early_bird' => $rule->min_days_before !== null && $daysUntilCheckin >= $rule->min_days_before,
                'long_stay' => $rule->min_nights !== null && $nights >= $rule->min_nights,
                'last_minute' => $rule->within_days !== null && $daysUntilCheckin <= $rule->within_days,
                default => false,
            };
        });

        return $qualifying->sortByDesc('discount_percentage')->first();
    }
}
