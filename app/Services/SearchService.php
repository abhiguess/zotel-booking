<?php

namespace App\Services;

use App\Models\DailyInventory;
use App\Models\RatePlan;
use App\Models\RatePlanDailyRate;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SearchService
{
    public function __construct(private DiscountService $discountService) {}

    /**
     * @return array{search_params: array, room_types: array}
     */
    public function search(string $checkIn, string $checkOut, int $adults): array
    {
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);
        $nights = (int) $checkInDate->diffInDays($checkOutDate);
        $daysUntilCheckin = (int) Carbon::today()->diffInDays($checkInDate, false);

        $roomTypes = RoomType::query()
            ->with([
                'dailyInventory' => function ($query) use ($checkIn, $checkOut) {
                    $query->where('date', '>=', $checkIn)
                        ->where('date', '<', $checkOut)
                        ->orderBy('date');
                },
                'ratePlans' => function ($query) {
                    $query->where('is_active', true)->orderBy('sort_order');
                },
                'ratePlans.dailyRates' => function ($query) use ($checkIn, $checkOut, $adults) {
                    $query->where('date', '>=', $checkIn)
                        ->where('date', '<', $checkOut)
                        ->where('occupancy', $adults)
                        ->orderBy('date');
                },
                'ratePlans.discountRules' => function ($query) {
                    $query->where('is_active', true);
                },
            ])
            ->get();

        $roomResults = $roomTypes->map(function (RoomType $roomType) use ($nights, $adults, $daysUntilCheckin) {
            return $this->buildRoomResult($roomType, $nights, $adults, $daysUntilCheckin);
        });

        return [
            'search_params' => [
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'nights' => $nights,
                'adults' => $adults,
                'days_until_checkin' => $daysUntilCheckin,
            ],
            'room_types' => $roomResults->toArray(),
        ];
    }

    /**
     * @return array{id: int, name: string, slug: string, description: string|null, max_adults: int, amenities: array|null, is_available: bool, available_rooms: int, unavailable_reason: string|null, rate_plans: array}
     */
    private function buildRoomResult(RoomType $roomType, int $nights, int $adults, int $daysUntilCheckin): array
    {
        $base = [
            'id' => $roomType->id,
            'name' => $roomType->name,
            'slug' => $roomType->slug,
            'description' => $roomType->description,
            'max_adults' => $roomType->max_adults,
            'amenities' => $roomType->amenities,
        ];

        // Occupancy check
        if ($adults > $roomType->max_adults) {
            return array_merge($base, [
                'is_available' => false,
                'available_rooms' => 0,
                'unavailable_reason' => "Exceeds maximum occupancy of {$roomType->max_adults} adults",
                'rate_plans' => [],
            ]);
        }

        // Availability check
        $availability = $this->checkAvailability($roomType->dailyInventory, $nights);

        if (! $availability['is_available']) {
            return array_merge($base, [
                'is_available' => false,
                'available_rooms' => 0,
                'unavailable_reason' => 'Sold Out',
                'rate_plans' => [],
            ]);
        }

        // Build rate plan results
        $ratePlanResults = $roomType->ratePlans
            ->map(function (RatePlan $ratePlan) use ($nights, $daysUntilCheckin) {
                return $this->buildRatePlanResult($ratePlan, $nights, $daysUntilCheckin);
            })
            ->filter()
            ->values()
            ->toArray();

        return array_merge($base, [
            'is_available' => true,
            'available_rooms' => $availability['available_rooms'],
            'unavailable_reason' => null,
            'rate_plans' => $ratePlanResults,
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
            return $day->is_blocked || $day->available_rooms < 1;
        });

        if ($hasBlockedOrFull) {
            return ['is_available' => false, 'available_rooms' => 0];
        }

        $availableRooms = $inventory->min(function (DailyInventory $day) {
            return $day->available_rooms;
        });

        return ['is_available' => true, 'available_rooms' => $availableRooms];
    }

    private function buildRatePlanResult(RatePlan $ratePlan, int $nights, int $daysUntilCheckin): ?array
    {
        $dailyRates = $ratePlan->dailyRates;

        // Missing pricing for some dates — rate plan unavailable for this search
        if ($dailyRates->count() < $nights) {
            return null;
        }

        $baseTotal = 0.0;
        $nightlyBreakdown = [];

        foreach ($dailyRates as $rate) {
            /** @var RatePlanDailyRate $rate */
            $price = (float) $rate->price;
            $baseTotal += $price;

            $nightlyBreakdown[] = [
                'date' => $rate->date->format('Y-m-d'),
                'day' => $rate->date->format('D'),
                'price' => $price,
            ];
        }

        $baseTotal = round($baseTotal, 2);

        $discount = $this->discountService->resolveBestDiscountForRatePlan(
            $ratePlan->id,
            $nights,
            $daysUntilCheckin,
            $baseTotal,
        );

        $finalTotal = round($baseTotal - $discount['amount'], 2);

        return [
            'id' => $ratePlan->id,
            'code' => $ratePlan->code,
            'name' => $ratePlan->name,
            'base_total' => $baseTotal,
            'discount' => $discount,
            'final_total' => $finalTotal,
            'nightly_breakdown' => $nightlyBreakdown,
        ];
    }
}
