<?php

namespace Database\Seeders;

use App\Models\RoomType;
use Illuminate\Database\Seeder;

class RoomTypeSeeder extends Seeder
{
    public function run(): void
    {
        $roomTypes = [
            [
                'name' => 'Standard Room',
                'slug' => 'standard',
                'description' => 'Clean, minimal space with queen bed, work desk, and natural light. 24 sqm.',
                'max_adults' => 3,
                'total_rooms' => 5,
                'amenities' => ['Queen Bed', 'Work Desk', 'Free WiFi', 'Air Conditioning', '24 sqm'],
            ],
            [
                'name' => 'Deluxe Room',
                'slug' => 'deluxe',
                'description' => 'Spacious room with king bed, seating area, and panoramic city views. 36 sqm.',
                'max_adults' => 4,
                'total_rooms' => 5,
                'amenities' => ['King Bed', 'Seating Area', 'City View', 'Free WiFi', 'Air Conditioning', 'Mini Bar', '36 sqm'],
            ],
        ];

        foreach ($roomTypes as $roomType) {
            RoomType::updateOrCreate(
                ['slug' => $roomType['slug']],
                $roomType,
            );
        }
    }
}
