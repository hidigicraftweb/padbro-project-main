<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookingController extends Controller
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Get user's bookings with optional status filter
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'nullable|string|in:upcoming,pending,completed,cancelled',
        ]);

        $bookings = $this->bookingService->getUserBookings(
            $request->user()->id,
            $request->input('status')
        );

        return response()->json([
            'success' => true,
            'data' => [
                'bookings' => $bookings->map(fn ($booking) => $this->bookingService->formatBookingResponse($booking)),
            ],
        ]);
    }

    /**
     * Create a new pending booking (locks the time slots)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'court_id' => 'required|integer|exists:courts,id',
            'date' => 'required|date|after_or_equal:today',
            'start_hour' => 'required|integer|min:0|max:23',
            'end_hour' => 'required|integer|min:1|max:24|gt:start_hour',
        ]);

        try {
            $booking = $this->bookingService->createPendingBooking(
                $request->user()->id,
                $request->input('court_id'),
                $request->input('date'),
                $request->input('start_hour'),
                $request->input('end_hour')
            );

            // Get the expiration time from the booking slots
            $expiresAt = $booking->bookingSlots()->first()?->expires_at;

            return response()->json([
                'success' => true,
                'data' => [
                    'booking' => $this->bookingService->formatBookingResponse($booking),
                    'expires_at' => $expiresAt?->toISOString(),
                    'expires_in_seconds' => $expiresAt ? now()->diffInSeconds($expiresAt, false) : null,
                ],
                'message' => 'Booking created successfully. Complete payment before it expires.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Submit proof of payment for a pending booking
     */
    public function confirm(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'proof_of_payment' => 'required|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
        ]);

        $booking = $this->bookingService->getBookingForUser($id, $request->user()->id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        try {
            // Store the proof of payment
            $path = $request->file('proof_of_payment')->store('proof-of-payments', 'public');

            $booking = $this->bookingService->submitProofOfPayment($booking, $path);

            return response()->json([
                'success' => true,
                'data' => [
                    'booking' => $this->bookingService->formatBookingResponse($booking),
                ],
                'message' => 'Payment proof submitted. Waiting for approval.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Cancel a pending booking and release slots
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $booking = $this->bookingService->getBookingForUser($id, $request->user()->id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        $cancelled = $this->bookingService->cancelBooking($booking);

        if (!$cancelled) {
            return response()->json([
                'success' => false,
                'message' => 'Booking cannot be cancelled',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully',
        ]);
    }

    /**
     * Get the user's next upcoming booking
     */
    public function nextBooking(Request $request): JsonResponse
    {
        $user = $request->user();

        $booking = $this->bookingService->getNextBooking($user->id);

        if (!$booking) {
            return response()->json([
                'success' => true,
                'data' => [
                    'booking' => null,
                ],
                'message' => 'No upcoming bookings found',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'booking' => $this->bookingService->formatBookingResponse($booking),
            ],
        ]);
    }
}
