<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminSlotController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => TimeSlot::orderBy('slot_time')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'slot_time' => ['required', 'date_format:H:i'],
            'capacity' => ['required', 'integer', 'min:0', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['slot_time'] .= ':00';

        if (TimeSlot::where('slot_time', $data['slot_time'])->exists()) {
            throw ValidationException::withMessages(['slot_time' => 'Slot jam tersebut sudah ada.']);
        }

        $slot = TimeSlot::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Slot berhasil dibuat.',
            'data' => $slot->fresh(),
        ], 201);
    }

    // Dipakai untuk mengubah kapasitas (jumlah karyawan) atau menonaktifkan slot
    public function update(Request $request, string $id): JsonResponse
    {
        $slot = TimeSlot::find($id);

        if (!$slot) {
            return response()->json(['success' => false, 'message' => 'Slot tidak ditemukan.'], 404);
        }

        $data = $request->validate([
            'capacity' => ['sometimes', 'integer', 'min:0', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $slot->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Slot berhasil diperbarui.',
            'data' => $slot->fresh(),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $slot = TimeSlot::find($id);

        if (!$slot) {
            return response()->json(['success' => false, 'message' => 'Slot tidak ditemukan.'], 404);
        }

        $slot->delete();

        return response()->json(['success' => true, 'message' => 'Slot berhasil dihapus.']);
    }
}