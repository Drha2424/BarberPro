<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    /**
     * Menampilkan daftar layanan aktif.
     */
    public function index(): JsonResponse
    {
        $services = Service::where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar layanan berhasil diambil.',
            'data' => $services,
        ], 200);
    }

    /**
     * Menampilkan detail satu layanan.
     */
    public function show(string $id): JsonResponse
    {
        $service = Service::where('is_active', true)
            ->find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail layanan berhasil diambil.',
            'data' => $service,
        ], 200);
    }
}