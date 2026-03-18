<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DiscountService;
use Illuminate\Http\JsonResponse;

class DiscountController extends Controller
{
    public function __construct(private DiscountService $discountService) {}

    public function __invoke(): JsonResponse
    {
        try {
            $grouped = $this->discountService->getActiveRulesGroupedByType();

            return response()->json([
                'success' => true,
                'data' => $grouped,
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
