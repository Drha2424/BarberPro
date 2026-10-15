<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminServiceController extends Controller
{
    // Semua layanan, termasuk yang nonaktif
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Service::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:600'],
            'icon' => ['nullable', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/'],
            'badge' => ['nullable', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $service = Service::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil dibuat.',
            'data' => $service->fresh(),
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json(['success' => false, 'message' => 'Layanan tidak ditemukan.'], 404);
        }

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'price' => ['sometimes', 'numeric', 'min:0', 'max:9999999999'],
            'duration_minutes' => ['sometimes', 'integer', 'min:5', 'max:600'],
            'icon' => ['sometimes', 'nullable', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/'],
            'badge' => ['sometimes', 'nullable', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $service->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil diperbarui.',
            'data' => $service->fresh(),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json(['success' => false, 'message' => 'Layanan tidak ditemukan.'], 404);
        }

        // Layanan yang sudah punya riwayat reservasi tidak boleh dihapus
        if (Reservation::where('service_id', $service->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan sudah punya riwayat reservasi. Nonaktifkan saja (is_active = false).',
            ], 409);
        }

        $service->delete();

        return response()->json(['success' => true, 'message' => 'Layanan berhasil dihapus.']);
    }
}