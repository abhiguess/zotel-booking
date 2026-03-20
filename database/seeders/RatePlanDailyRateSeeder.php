<?php

namespace Database\Seeders;

use App\Models\RatePlan;
use App\Models\RatePlanDailyRate;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RatePlanDailyRateSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();

        $ratePlans = RatePlan::with('roomType')->get();

        /**
         * @var array<string, array<int, float>>
         *
         * Key format: "{room_type_slug}_{rate_plan_code}"
         * Value: occupancy => price per night
         */
        $pricing = [
            'standard_EP' => [
                1 => 2000.00,
                2 => 2500.00,
                3 => 3000.00,
            ],
            'standard_CP' => [
                1 => 2400.00,
                2 => 3300.00,
                3 => 4200.00,
            ],
            'deluxe_CP' => [
                1 => 3500.00,
                2 => 4500.00,
                3 => 5500.00,
                4 => 6500.00,
            ],
            'deluxe_MAP' => [
                1 => 3900.00,
                2 => 5300.00,
                3 => 6700.00,
                4 => 8100.00,
            ],
        ];

        foreach ($ratePlans as $ratePlan) {
            $key = $ratePlan->roomType->slug.'_'.$ratePlan->code;
            $occupancyPrices = $pricing[$key] ?? null;

            if (! $occupancyPrices) {
                continue;
            }

            $rows = [];

            for ($i = 0; $i < 30; $i++) {
                $date = $today->copy()->addDays($i)->toDateString();

                foreach ($occupancyPrices as $occupancy => $price) {
                    $rows[] = [
                        'rate_plan_id' => $ratePlan->id,
                        'date' => $date,
                        'occupancy' => $occupancy,
                        'price' => $price,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            RatePlanDailyRate::upsert(
                $rows,
                ['rate_plan_id', 'date', 'occupancy'],
                ['price', 'updated_at'],
            );
        }
    }
}
