<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $plans = Plan::query()->orderBy('price_cents')->paginate((int)$request->get('per_page', 10));
        return $this->response->statusOk([
            'plans' => $plans->items(),
            'pagination' => [
                'current_page' => $plans->currentPage(),
                'per_page' => $plans->perPage(),
                'total' => $plans->total(),
                'last_page' => $plans->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'price_cents' => 'required|integer|min:0',
            'currency' => 'nullable|string|max:10',
            'interval' => 'required|string|in:month,year',
            'stripe_price_id' => 'nullable|string|max:255',
        ]);
        if (!isset($validated['currency'])) {
            $validated['currency'] = 'USD';
        }
        $plan = Plan::create($validated);
        return $this->response->statusOk(['plan' => $plan], 201);
    }

    public function show($id): JsonResponse
    {
        $plan = Plan::find($id);
        if (!$plan) {
            return $this->response->notFound(['message' => 'Plan not found'], 404);
        }
        return $this->response->statusOk(['plan' => $plan]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $plan = Plan::find($id);
        if (!$plan) {
            return $this->response->notFound(['message' => 'Plan not found'], 404);
        }
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'price_cents' => 'nullable|integer|min:0',
            'currency' => 'nullable|string|max:10',
            'interval' => 'nullable|string|in:month,year',
            'stripe_price_id' => 'nullable|string|max:255',
        ]);
        $plan->fill($validated)->save();
        return $this->response->statusOk(['plan' => $plan]);
    }

    public function destroy($id): JsonResponse
    {
        $plan = Plan::find($id);
        if (!$plan) {
            return $this->response->notFound(['message' => 'Plan not found'], 404);
        }
        $plan->delete();
        return $this->response->statusOk(['message' => 'Deleted', 'id' => (int)$id]);
    }
}

