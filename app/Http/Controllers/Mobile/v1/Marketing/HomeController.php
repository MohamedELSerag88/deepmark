<?php

namespace App\Http\Controllers\Mobile\v1\Marketing;

use App\Http\Controllers\Controller;
use App\Services\Marketing\MarketingHomeService;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    public function index(MarketingHomeService $service): JsonResponse
    {
        return $this->response->statusOk([
            'data' => $service->build(),
        ]);
    }
}
