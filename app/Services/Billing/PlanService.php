<?php

namespace App\Services\Billing;

use App\Models\Plan;
use Illuminate\Support\Collection;

class PlanService
{
	public function list(): Collection
	{
		return Plan::with('features')->orderBy('price_cents')->get();
	}
}
