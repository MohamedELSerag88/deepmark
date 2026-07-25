<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\ShareBrandNamesRequest;
use App\Http\Resources\Mobile\MessageResource;
use App\Services\Brand\BrandShareService;
use Illuminate\Http\JsonResponse;

class BrandNameShareController extends Controller
{
	public function __construct(
		private readonly BrandShareService $brandShareService,
	) {
		parent::__construct();
	}

	public function share(ShareBrandNamesRequest $request): JsonResponse
	{
		$this->brandShareService->share(
			[
				'emails' => (array) $request->input('emails', []),
				'subject' => $request->input('subject'),
				'message' => $request->input('message', ''),
				'names' => (array) $request->input('names', []),
				'brand_chat_id' => $request->input('brand_chat_id'),
			],
			auth('api')->id()
		);

		return $this->statusOk(new MessageResource(['message' => 'Shared successfully']));
	}
}
