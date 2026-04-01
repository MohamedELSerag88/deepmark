<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrandChat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BrandChat::query();
        // Sort by join date
        $query->orderByDesc('id');

        $brands = $query->paginate((int)$request->get('per_page', 10));

        return $this->response->statusOk([
            'brands' => $brands->items(),
            'pagination' => [
                'current_page' => $brands->currentPage(),
                'per_page' => $brands->perPage(),
                'total' => $brands->total(),
                'last_page' => $brands->lastPage(),
            ],
        ]);
    }

    public function show($id): JsonResponse
    {
        $brand = BrandChat::find($id);
        if (!$brand) {
            return $this->response->notFound(['message' => 'Brand not found'], 404);
        }
        return $this->response->statusOk([
            'brand' => $brand
        ]);
    }
}

