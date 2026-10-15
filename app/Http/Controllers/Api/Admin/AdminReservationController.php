<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminReservationController extends Controller
{
    private const STATUSES = ['Pending', 'Confirmed', 'Cancelled', 'Completed'];
    private const PAYMENT_STATUSES = ['Pending', 'Paid', 'Refunded'];

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'date' => ['sometimes', 'date_format:Y-m-d'],
            'status' => ['sometimes', Rule::in(self::STATUSES)],
            'payment_status' => ['sometimes', Rule::in(self::PAYMENT_STATUSES)],
        ]);

        $query = Reservation::with('service')
            ->orderByDesc('booking_date')
            ->orderByDesc('booking_time');

        if (isset($filters['date'])) {
            $query->where('booking_date', $filters['date']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate(20),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $reservation = Reservation::with('service')->find($id);

        if (!$reservation) {
            return response()->json(['success' => false, 'message' => 'Reservasi tidak ditemukan.'], 404);
        }

        return response()->json(['success' => true, 'data' => $reservation]);
    }

    // Admin hanya boleh mengubah status, bukan data pelanggan atau harga
    public function update(Request $request, string $id): JsonResponse
    {
        $reservation = Reservation::find($id);

        if (!$reservation) {
            return response()->json(['success' => false, 'message' => 'Reservasi tidak ditemukan.'], 404);
        }

        $data = $request->validate([
            'status' => ['sometimes', Rule::in(self::STATUSES)],
            'payment_status' => ['sometimes', Rule::in(self::PAYMENT_STATUSES)],
        ]);

        $reservation->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Reservasi berhasil diperbarui.',
            'data' => $reservation->fresh()->load('service'),
        ]);
    }
}