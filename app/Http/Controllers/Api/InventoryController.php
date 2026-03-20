<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $today = Carbon::today();
            $endDate = $today->copy()->addDays(30);

            $query = RoomType::query()
                ->with([
                    'dailyInventory' => function ($query) use ($today, $endDate) {
                        $query->where('date', '>=', $today->toDateString())
                            ->where('date', '<', $endDate->toDateString())
                            ->orderBy('date');
                    },
                    'ratePlans' => function ($query) {
                        $query->where('is_active', true)->orderBy('sort_order');
                    },
                    'ratePlans.dailyRates' => function ($query) use ($today, $endDate) {
                        $query->where('date', '>=', $today->toDateString())
                            ->where('date', '<', $endDate->toDateString())
                            ->orderBy('date')
                            ->orderBy('occupancy');
                    },
                ]);

            if ($request->has('room_type_slug')) {
                $query->where('slug', $request->input('room_type_slug'));
            }

            $roomTypes = $query->get();

            $data = $roomTypes->map(function (RoomType $roomType) {
                $inventoryByDate = $roomType->dailyInventory->keyBy(fn ($day) => $day->date->format('Y-m-d'));

                return [
                    'id' => $roomType->id,
                    'name' => $roomType->name,
                    'slug' => $roomType->slug,
                    'max_adults' => $roomType->max_adults,
                    'rate_plans' => $roomType->ratePlans->map(function ($ratePlan) use ($inventoryByDate) {
                        $ratesByDate = $ratePlan->dailyRates->groupBy(fn ($rate) => $rate->date->format('Y-m-d'));

                        return [
                            'id' => $ratePlan->id,
                            'code' => $ratePlan->code,
                            'name' => $ratePlan->name,
                            'daily_rates' => $ratesByDate->map(function ($rates, $dateStr) use ($inventoryByDate) {
                                $inventory = $inventoryByDate[$dateStr] ?? null;

                                return [
                                    'date' => $dateStr,
                                    'day' => Carbon::parse($dateStr)->format('D'),
                                    'available_rooms' => $inventory ? $inventory->available_rooms : 0,
                                    'total_rooms' => $inventory ? $inventory->total_rooms : 0,
                                    'is_blocked' => $inventory ? $inventory->is_blocked : false,
                                    'occupancy_rates' => $rates->map(fn ($rate) => [
                                        'occupancy' => $rate->occupancy,
                                        'price' => (float) $rate->price,
                                    ])->values(),
                                ];
                            })->values(),
                        ];
                    }),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }
}
