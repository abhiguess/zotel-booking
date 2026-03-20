<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DiscountService;
use Illuminate\Http\JsonResponse;

class DiscountController extends Controller
{
    public function __construct(private DiscountService $discountService) {}

    public function __invoke(): JsonResponse
    {
        try {
            $rules = $this->discountService->getActiveRulesWithRatePlans();

            $formatted = $rules->map(function ($rule) {
                $ratePlan = $rule->ratePlan;
                $roomTypeName = $ratePlan?->roomType?->name ?? 'Global';

                $condition = match ($rule->type) {
                    'early_bird' => "Book {$rule->min_days_before}+ days before check-in",
                    'long_stay' => "{$rule->min_nights}+ nights stay",
                    'last_minute' => "Check-in within {$rule->within_days} days",
                    default => '',
                };

                return [
                    'id' => $rule->id,
                    'rate_plan' => $ratePlan
                        ? "{$ratePlan->code} - {$ratePlan->name} ({$roomTypeName})"
                        : 'Global',
                    'type' => $rule->type,
                    'name' => $rule->name,
                    'percentage' => (float) $rule->discount_percentage,
                    'condition' => $condition,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'discount_rules' => $formatted->values(),
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }
}
