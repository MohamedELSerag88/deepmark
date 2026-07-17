<?php

namespace App\Http\Controllers\Mobile\v1\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\Marketing\FaqResource;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;

class FaqController extends Controller
{
    public function index(): JsonResponse
    {
        $faqs = Faq::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return $this->response->statusOk([
            'data' => FaqResource::collection($faqs),
        ]);
    }
}
