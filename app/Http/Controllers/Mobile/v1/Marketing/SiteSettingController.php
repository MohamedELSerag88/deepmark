<?php

namespace App\Http\Controllers\Mobile\v1\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\Marketing\SiteSettingResource;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class SiteSettingController extends Controller
{
    public function show(): JsonResponse
    {
        return $this->statusOk([
            'data' => new SiteSettingResource(SiteSetting::current()),
        ]);
    }
}
