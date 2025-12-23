<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\PlanResource;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;

class PlanController extends Controller {


	public function index(): JsonResponse
	{
		$plans = Plan::with('features')->orderBy('price_cents')->get();
		return $this->response->statusOk([
			'data' => PlanResource::collection($plans)
		]);
	}
}


