<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CourtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourtController extends Controller
{
    protected CourtService $courtService;

    public function __construct(CourtService $courtService)
    {
        $this->courtService = $courtService;
    }

    /**
     * Get paginated list of courts with filters
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'city_id' => 'nullable|integer|exists:cities,id',
            'category_id' => 'nullable|integer|exists:categories,id',
            'material' => 'nullable|string|in:grass,cement',
            'search' => 'nullable|string|max:100',
            'min_price' => 'nullable|integer|min:0',
            'max_price' => 'nullable|integer|min:0',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $filters = $request->only([
            'city_id',
            'category_id',
            'material',
            'search',
            'min_price',
            'max_price',
        ]);

        $perPage = $request->input('per_page', 10);

        $courts = $this->courtService->getPaginated($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'courts' => $courts->map(fn ($court) => $this->courtService->formatCourtListItem($court)),
                'pagination' => [
                    'current_page' => $courts->currentPage(),
                    'last_page' => $courts->lastPage(),
                    'per_page' => $courts->perPage(),
                    'total' => $courts->total(),
                ],
            ],
        ]);
    }

    /**
     * Get court details
     */
    public function show(int $id): JsonResponse
    {
        $court = $this->courtService->findById($id);

        if (!$court) {
            return response()->json([
                'success' => false,
                'message' => 'Court not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'court' => $this->courtService->formatCourtResponse($court),
            ],
        ]);
    }

    /**
     * Get available time slots for a court on a specific date
     */
    public function availableSlots(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
        ]);

        $court = $this->courtService->findById($id);

        if (!$court) {
            return response()->json([
                'success' => false,
                'message' => 'Court not found',
            ], 404);
        }

        $slots = $this->courtService->getAvailableSlots($court, $request->input('date'));

        return response()->json([
            'success' => true,
            'data' => [
                'court_id' => $court->id,
                'court_name' => $court->name,
                'date' => $request->input('date'),
                'price_per_hour' => $court->price_per_hour,
                'slots' => $slots,
            ],
        ]);
    }

    /**
     * Get popular courts
     */
    public function popular(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 5);
        $courts = $this->courtService->getPopular($limit);

        return response()->json([
            'success' => true,
            'data' => [
                'courts' => $courts->map(fn ($court) => $this->courtService->formatCourtListItem($court)),
            ],
        ]);
    }

    /**
     * Get featured courts
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 5);
        $courts = $this->courtService->getFeatured($limit);

        return response()->json([
            'success' => true,
            'data' => [
                'courts' => $courts->map(fn ($court) => $this->courtService->formatCourtListItem($court)),
            ],
        ]);
    }
}
