<?php

namespace Database\Seeders;

use App\Models\DailyInventory;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DailyInventorySeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();

        /** @var array<string, array{price_1p: float, price_2p: float, price_3p: float, breakfast_price_pp: float}> */
        $pricing = [
            'standard' => [
                'price_1p' => 2000.00,
                'price_2p' => 2500.00,
                'price_3p' => 3000.00,
                'breakfast_price_pp' => 400.00,
            ],
            'deluxe' => [
                'price_1p' => 3000.00,
                'price_2p' => 3500.00,
                'price_3p' => 4000.00,
                'breakfast_price_pp' => 500.00,
            ],
        ];

        /** @var array<string, array<int, int>> */
        $soldOutOverrides = [
            'standard' => [5 => 5, 6 => 5, 20 => 4],
            'deluxe' => [12 => 5, 13 => 5, 20 => 3],
        ];

        $roomTypes = RoomType::all()->keyBy('slug');

        foreach ($roomTypes as $slug => $roomType) {
            $rows = [];

            for ($i = 0; $i < 30; $i++) {
                $date = $today->copy()->addDays($i);
                $bookedRooms = $soldOutOverrides[$slug][$i] ?? 0;

                $rows[] = [
                    'room_type_id' => $roomType->id,
                    'date' => $date->toDateString(),
                    'price_1p' => $pricing[$slug]['price_1p'],
                    'price_2p' => $pricing[$slug]['price_2p'],
                    'price_3p' => $pricing[$slug]['price_3p'],
                    'breakfast_price_pp' => $pricing[$slug]['breakfast_price_pp'],
                    'total_rooms' => $roomType->total_rooms,
                    'booked_rooms' => $bookedRooms,
                    'is_blocked' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DailyInventory::upsert(
                $rows,
                ['room_type_id', 'date'],
                ['price_1p', 'price_2p', 'price_3p', 'breakfast_price_pp', 'total_rooms', 'booked_rooms', 'is_blocked', 'updated_at'],
            );
        }
    }
}
