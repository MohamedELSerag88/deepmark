<?php

namespace App\Http\Controllers\Mobile\v1\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\Marketing\PricingPackageResource;
use App\Models\PricingPackage;
use Illuminate\Http\JsonResponse;

class PricingPackageController extends Controller
{
    public function index(): JsonResponse
    {
        $packages = PricingPackage::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return $this->statusOk([
            'data' => PricingPackageResource::collection($packages),
        ]);
    }
}
