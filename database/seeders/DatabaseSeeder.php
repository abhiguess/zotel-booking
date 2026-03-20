<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoomTypeSeeder::class,
            RatePlanSeeder::class,
            RatePlanDailyRateSeeder::class,
            DailyInventorySeeder::class,
            DiscountRuleSeeder::class,
        ]);
    }
}
