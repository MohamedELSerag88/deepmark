<?php

namespace App\Http\Controllers\Mobile\v1\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\Marketing\MarketingHomeResource;
use App\Services\Marketing\MarketingHomeService;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
	public function index(MarketingHomeService $service): JsonResponse
	{
		return $this->okResource(new MarketingHomeResource($service->build()));
	}
}
