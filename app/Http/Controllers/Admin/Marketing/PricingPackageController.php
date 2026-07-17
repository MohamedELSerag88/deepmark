<?php

namespace App\Http\Controllers\Admin\Marketing;

use App\Http\Controllers\Controller;
use App\Models\PricingPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PricingPackageController extends Controller
{
    public function index(): JsonResponse
    {
        $packages = PricingPackage::query()->orderBy('sort_order')->get();
        return $this->response->statusOk(['packages' => $packages]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules());
        $package = PricingPackage::create($validated);
        return $this->response->statusOk(['package' => $package], 201);
    }

    public function show($id): JsonResponse
    {
        $package = PricingPackage::find($id);
        if (!$package) {
            return $this->response->notFound(['message' => 'Pricing package not found'], 404);
        }
        return $this->response->statusOk(['package' => $package]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $package = PricingPackage::find($id);
        if (!$package) {
            return $this->response->notFound(['message' => 'Pricing package not found'], 404);
        }
        $validated = $request->validate($this->rules($id));
        $package->update($validated);
        return $this->response->statusOk(['package' => $package->fresh()]);
    }

    public function destroy($id): JsonResponse
    {
        $package = PricingPackage::find($id);
        if (!$package) {
            return $this->response->notFound(['message' => 'Pricing package not found'], 404);
        }
        $package->delete();
        return $this->response->statusOk(['message' => 'Deleted', 'id' => (int) $id]);
    }

    protected function rules($id = null): array
    {
        $slug = $id
            ? 'sometimes|required|string|max:150|unique:pricing_packages,slug,' . $id
            : 'required|string|max:150|unique:pricing_packages,slug';

        return [
            'slug' => $slug,
            'name_en' => ($id ? 'sometimes|' : '') . 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'price_display' => 'nullable|string|max:50',
            'currency_symbol' => 'nullable|string|max:10',
            'description_en' => 'nullable|string|max:2000',
            'description_ar' => 'nullable|string|max:2000',
            'features_en' => 'nullable|array',
            'features_ar' => 'nullable|array',
            'badge_en' => 'nullable|string|max:100',
            'badge_ar' => 'nullable|string|max:100',
            'is_recommended' => 'nullable|boolean',
            'cta_label_en' => 'nullable|string|max:100',
            'cta_label_ar' => 'nullable|string|max:100',
            'cta_url' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }
}
