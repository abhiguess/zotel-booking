<?php

namespace Database\Seeders;

use App\Models\DiscountRule;
use Illuminate\Database\Seeder;

class DiscountRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'name' => 'Long Stay 3+ Nights',
                'type' => 'long_stay',
                'min_nights' => 3,
                'within_days' => null,
                'discount_percentage' => 10.00,
                'priority' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Long Stay 6+ Nights',
                'type' => 'long_stay',
                'min_nights' => 6,
                'within_days' => null,
                'discount_percentage' => 20.00,
                'priority' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Last Minute Deal',
                'type' => 'last_minute',
                'min_nights' => null,
                'within_days' => 3,
                'discount_percentage' => 5.00,
                'priority' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($rules as $rule) {
            DiscountRule::updateOrCreate(
                ['name' => $rule['name'], 'type' => $rule['type']],
                $rule,
            );
        }
    }
}
