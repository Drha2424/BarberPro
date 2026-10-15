<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\TimeSlot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    /**
     * Daftar slot untuk satu tanggal beserta sisa kapasitasnya.
     */
    public function availability(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        ]);

        $slots = TimeSlot::where('is_active', true)->orderBy('slot_time')->get();

        $taken = Reservation::active()
            ->where('booking_date', $data['date'])
            ->selectRaw('booking_time, count(*) as total')
            ->groupBy('booking_time')
            ->pluck('total', 'booking_time');

        $result = $slots->map(function ($slot) use ($taken) {
            $remaining = max(0, $slot->capacity - (int) ($taken[$slot->slot_time] ?? 0));

            return [
                'time' => substr($slot->slot_time, 0, 5),
                'remaining' => $remaining,
                'available' => $remaining > 0,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar slot berhasil diambil.',
            'data' => $result,
        ], 200);
    }

    /**
     * Pelanggan yang sedang login membuat reservasi.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'service_id' => ['required', 'uuid'],
            'booking_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'booking_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', Rule::in(['CASH', 'QRIS'])],
        ]);

        if (!$user->phone) {
            throw ValidationException::withMessages([
                'phone' => 'Nomor telepon di akun belum diisi.',
            ]);
        }

        $service = Service::where('is_active', true)->find($data['service_id']);

        if (!$service) {
            throw ValidationException::withMessages([
                'service_id' => 'Layanan tidak ditemukan atau tidak aktif.',
            ]);
        }

        $time = $data['booking_time'] . ':00';

        $reservation = DB::transaction(function () use ($data, $service, $time, $user) {
            // Kunci baris slot supaya dua pelanggan tidak bisa merebut slot terakhir bersamaan
            $slot = TimeSlot::where('slot_time', $time)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (!$slot) {
                throw ValidationException::withMessages([
                    'booking_time' => 'Jam tersebut tidak tersedia.',
                ]);
            }

            $taken = Reservation::active()
                ->where('booking_date', $data['booking_date'])
                ->where('booking_time', $time)
                ->count();

            if ($taken >= $slot->capacity) {
                throw ValidationException::withMessages([
                    'booking_time' => 'Slot pada jam tersebut sudah penuh.',
                ]);
            }

            return Reservation::create([
                'user_id' => $user->id,
                'booking_code' => $this->generateBookingCode(),
                'customer_name' => $user->name,
                'customer_phone' => $user->phone,
                'service_id' => $service->id,
                'booking_date' => $data['booking_date'],
                'booking_time' => $time,
                'notes' => $data['notes'] ?? null,
                'payment_method' => $data['payment_method'],
                'total_price' => $service->price, // dari database, bukan dari request
                'status' => 'Pending',
                'payment_status' => 'Pending',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Reservasi berhasil dibuat.',
            'data' => $reservation->load('service'),
        ], 201);
    }

    /**
     * Daftar reservasi milik pelanggan yang sedang login.
     */
    public function mine(Request $request): JsonResponse
    {
        $reservations = Reservation::with('service:id,name')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('booking_date')
            ->orderByDesc('booking_time')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $reservations,
        ], 200);
    }

    /**
     * Detail satu reservasi milik pelanggan yang sedang login.
     */
    public function showMine(Request $request, string $id): JsonResponse
    {
        $reservation = Reservation::with('service:id,name')
            ->where('user_id', $request->user()->id)
            ->find($id);

        // Reservasi milik orang lain juga dijawab 404, supaya keberadaannya tidak bocor
        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reservasi tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $reservation,
        ], 200);
    }

    private function generateBookingCode(): string
    {
        do {
            $code = 'BP-' . strtoupper(Str::random(6));
        } while (Reservation::where('booking_code', $code)->exists());

        return $code;
    }
}