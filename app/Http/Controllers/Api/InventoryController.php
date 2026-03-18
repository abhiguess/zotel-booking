<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyInventory;
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
                ->with(['dailyInventory' => function ($query) use ($today, $endDate) {
                    $query->where('date', '>=', $today->toDateString())
                        ->where('date', '<', $endDate->toDateString())
                        ->orderBy('date');
                }]);

            if ($request->has('room_type_slug')) {
                $query->where('slug', $request->input('room_type_slug'));
            }

            $roomTypes = $query->get();

            $data = $roomTypes->map(function (RoomType $roomType) {
                return [
                    'id' => $roomType->id,
                    'name' => $roomType->name,
                    'slug' => $roomType->slug,
                    'inventory' => $roomType->dailyInventory->map(function (DailyInventory $day) {
                        return [
                            'date' => $day->date->format('Y-m-d'),
                            'day' => $day->date->format('D'),
                            'available_rooms' => $day->total_rooms - $day->booked_rooms,
                            'total_rooms' => $day->total_rooms,
                            'price_1p' => (float) $day->price_1p,
                            'price_2p' => (float) $day->price_2p,
                            'price_3p' => (float) $day->price_3p,
                            'breakfast_price_pp' => (float) $day->breakfast_price_pp,
                            'is_blocked' => $day->is_blocked,
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
