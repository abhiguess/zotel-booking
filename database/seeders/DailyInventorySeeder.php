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
                ['total_rooms', 'booked_rooms', 'is_blocked', 'updated_at'],
            );
        }
    }
}
