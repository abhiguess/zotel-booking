<?php

namespace Database\Seeders;

use App\Models\RatePlan;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

class RatePlanSeeder extends Seeder
{
    public function run(): void
    {
        $roomTypes = RoomType::all()->keyBy('slug');

        $ratePlans = [
            [
                'room_type_slug' => 'standard',
                'name' => 'Room Only',
                'slug' => 'room-only',
                'code' => 'EP',
                'description' => 'Room accommodation without meals.',
                'sort_order' => 1,
            ],
            [
                'room_type_slug' => 'standard',
                'name' => 'Breakfast Included',
                'slug' => 'breakfast-included',
                'code' => 'CP',
                'description' => 'Room accommodation with daily breakfast.',
                'sort_order' => 2,
            ],
            [
                'room_type_slug' => 'deluxe',
                'name' => 'Breakfast Included',
                'slug' => 'breakfast-included',
                'code' => 'CP',
                'description' => 'Room accommodation with daily breakfast.',
                'sort_order' => 1,
            ],
            [
                'room_type_slug' => 'deluxe',
                'name' => 'All Meals Included',
                'slug' => 'all-meals-included',
                'code' => 'MAP',
                'description' => 'Room accommodation with breakfast, lunch, and dinner.',
                'sort_order' => 2,
            ],
        ];

        foreach ($ratePlans as $plan) {
            $roomType = $roomTypes[$plan['room_type_slug']];

            RatePlan::updateOrCreate(
                ['room_type_id' => $roomType->id, 'code' => $plan['code']],
                [
                    'name' => $plan['name'],
                    'slug' => $plan['slug'],
                    'description' => $plan['description'],
                    'sort_order' => $plan['sort_order'],
                    'is_active' => true,
                ],
            );
        }
    }
}
