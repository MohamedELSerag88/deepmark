<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\PlanResource;
use App\Services\Billing\PlanService;
use Illuminate\Http\JsonResponse;

class PlanController extends Controller
{
	public function __construct(
		private readonly PlanService $planService,
	) {
		parent::__construct();
	}

	public function index(): JsonResponse
	{
		return $this->okResource(
			PlanResource::collection($this->planService->list())
		);
	}
}
