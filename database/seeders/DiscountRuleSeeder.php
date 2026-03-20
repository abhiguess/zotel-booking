<?php

namespace Database\Seeders;

use App\Models\DiscountRule;
use App\Models\RatePlan;
use Illuminate\Database\Seeder;

class DiscountRuleSeeder extends Seeder
{
    public function run(): void
    {
        // Deactivate all Round 1 global discount rules
        DiscountRule::query()
            ->whereNull('rate_plan_id')
            ->update(['is_active' => false]);

        $ratePlans = RatePlan::with('roomType')->get();

        /** @var array<string, array{percentage: float}> */
        $earlyBirdConfig = [
            'standard_EP' => ['percentage' => 5.00],
            'standard_CP' => ['percentage' => 10.00],
            'deluxe_CP' => ['percentage' => 10.00],
            'deluxe_MAP' => ['percentage' => 10.00],
        ];

        foreach ($ratePlans as $ratePlan) {
            $key = $ratePlan->roomType->slug.'_'.$ratePlan->code;
            $config = $earlyBirdConfig[$key] ?? null;

            if (! $config) {
                continue;
            }

            $roomTypeName = $ratePlan->roomType->name;
            $percentage = $config['percentage'];

            DiscountRule::updateOrCreate(
                ['rate_plan_id' => $ratePlan->id, 'type' => 'early_bird'],
                [
                    'name' => "Early Bird {$percentage}% - {$ratePlan->code} ({$roomTypeName})",
                    'min_nights' => null,
                    'within_days' => null,
                    'min_days_before' => 7,
                    'discount_percentage' => $percentage,
                    'priority' => 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
