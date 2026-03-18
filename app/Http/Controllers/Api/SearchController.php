<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    public function __construct(private SearchService $searchService) {}

    public function __invoke(SearchRequest $request): JsonResponse
    {
        try {
            $results = $this->searchService->search(
                $request->validated('check_in'),
                $request->validated('check_out'),
                (int) $request->validated('adults'),
            );

            return response()->json([
                'success' => true,
                'data' => $results,
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
