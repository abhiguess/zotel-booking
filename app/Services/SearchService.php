<?php

namespace App\Services;

use App\Models\DailyInventory;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SearchService
{
    /** @var array<int, string> */
    private const PRICE_COLUMN_MAP = [
        1 => 'price_1p',
        2 => 'price_2p',
        3 => 'price_3p',
    ];

    public function __construct(private DiscountService $discountService) {}

    /**
     * @return array{search_params: array, discount: array, room_types: array}
     */
    public function search(string $checkIn, string $checkOut, int $adults): array
    {
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);
        $nights = (int) $checkInDate->diffInDays($checkOutDate);
        $daysUntilCheckin = (int) Carbon::today()->diffInDays($checkInDate, false);
        $priceColumn = self::PRICE_COLUMN_MAP[$adults];

        $discount = $this->discountService->resolveBestDiscount($nights, $daysUntilCheckin);

        $roomTypes = RoomType::query()
            ->with(['dailyInventory' => function ($query) use ($checkInDate, $checkOutDate) {
                $query->where('date', '>=', $checkInDate->toDateString())
                    ->where('date', '<', $checkOutDate->toDateString())
                    ->orderBy('date');
            }])
            ->get();

        $roomResults = $roomTypes->map(function (RoomType $roomType) use ($nights, $priceColumn, $discount, $adults) {
            return $this->buildRoomResult($roomType, $nights, $priceColumn, $discount, $adults);
        });

        return [
            'search_params' => [
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'nights' => $nights,
                'adults' => $adults,
                'days_until_checkin' => $daysUntilCheckin,
            ],
            'discount' => $discount,
            'room_types' => $roomResults->toArray(),
        ];
    }

    /**
     * @return array{id: int, name: string, slug: string, description: string|null, max_adults: int, amenities: array|null, is_available: bool, available_rooms: int, pricing: array|null, nightly_breakdown: array|null}
     */
    private function buildRoomResult(RoomType $roomType, int $nights, string $priceColumn, array $discount, int $adults): array
    {
        $inventory = $roomType->dailyInventory;

        $base = [
            'id' => $roomType->id,
            'name' => $roomType->name,
            'slug' => $roomType->slug,
            'description' => $roomType->description,
            'max_adults' => $roomType->max_adults,
            'amenities' => $roomType->amenities,
        ];

        $availability = $this->checkAvailability($inventory, $nights);

        if (! $availability['is_available']) {
            return array_merge($base, [
                'is_available' => false,
                'available_rooms' => 0,
                'pricing' => null,
                'nightly_breakdown' => null,
            ]);
        }

        $pricing = $this->calculatePricing($inventory, $priceColumn, $discount, $adults);

        return array_merge($base, [
            'is_available' => true,
            'available_rooms' => $availability['available_rooms'],
            'pricing' => $pricing['totals'],
            'nightly_breakdown' => $pricing['breakdown'],
        ]);
    }

    /**
     * @return array{is_available: bool, available_rooms: int}
     */
    private function checkAvailability(Collection $inventory, int $nights): array
    {
        if ($inventory->count() < $nights) {
            return ['is_available' => false, 'available_rooms' => 0];
        }

        $hasBlockedOrFull = $inventory->contains(function (DailyInventory $day) {
            return $day->is_blocked || ($day->total_rooms - $day->booked_rooms) < 1;
        });

        if ($hasBlockedOrFull) {
            return ['is_available' => false, 'available_rooms' => 0];
        }

        $availableRooms = $inventory->min(function (DailyInventory $day) {
            return $day->total_rooms - $day->booked_rooms;
        });

        return ['is_available' => true, 'available_rooms' => $availableRooms];
    }

    /**
     * @return array{totals: array{room_only: array, with_breakfast: array}, breakdown: array}
     */
    private function calculatePricing(Collection $inventory, string $priceColumn, array $discount, int $adults): array
    {
        $discountPercentage = $discount['percentage'] ?? 0.0;
        $breakdown = [];
        $roomOnlyBaseTotal = 0.0;
        $withBreakfastBaseTotal = 0.0;

        foreach ($inventory as $day) {
            $roomRate = (float) $day->{$priceColumn};
            $breakfastTotal = round((float) $day->breakfast_price_pp * $adults, 2);

            $breakdown[] = [
                'date' => $day->date->format('Y-m-d'),
                'day' => $day->date->format('D'),
                'room_rate' => $roomRate,
                'breakfast_total' => $breakfastTotal,
            ];

            $roomOnlyBaseTotal += $roomRate;
            $withBreakfastBaseTotal += ($roomRate + $breakfastTotal);
        }

        $roomOnlyBaseTotal = round($roomOnlyBaseTotal, 2);
        $withBreakfastBaseTotal = round($withBreakfastBaseTotal, 2);

        $roomOnlyDiscount = round($roomOnlyBaseTotal * $discountPercentage / 100, 2);
        $withBreakfastDiscount = round($withBreakfastBaseTotal * $discountPercentage / 100, 2);

        return [
            'totals' => [
                'room_only' => [
                    'base_total' => $roomOnlyBaseTotal,
                    'discount_percentage' => $discountPercentage,
                    'discount_amount' => $roomOnlyDiscount,
                    'final_total' => round($roomOnlyBaseTotal - $roomOnlyDiscount, 2),
                ],
                'with_breakfast' => [
                    'base_total' => $withBreakfastBaseTotal,
                    'discount_percentage' => $discountPercentage,
                    'discount_amount' => $withBreakfastDiscount,
                    'final_total' => round($withBreakfastBaseTotal - $withBreakfastDiscount, 2),
                ],
            ],
            'breakdown' => $breakdown,
        ];
    }
}
