<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\CheckDomainRequest;
use App\Http\Requests\Mobile\CreateBrandTextRequest;
use App\Http\Requests\Mobile\EditBrandTextRequest;
use App\Http\Requests\Mobile\ReserveDomainRequest;
use App\Http\Resources\Mobile\BrandTextHistoryResource;
use App\Http\Resources\Mobile\BrandTextResource;
use App\Http\Resources\Mobile\DomainCheckResultResource;
use App\Http\Resources\Mobile\DomainReservationResource;
use App\Services\Brand\BrandTextService;
use Illuminate\Http\JsonResponse;

class BrandTextController extends Controller
{
	public function __construct(
		private readonly BrandTextService $brandTextService,
	) {
		parent::__construct();
	}

	public function generate(CreateBrandTextRequest $request): JsonResponse
	{
		try {
			$result = $this->brandTextService->generate(
				[
					'answers' => $request->input('answers', []),
					'language' => $request->input('language', 'both'),
				],
				auth('api')->id()
			);

			return $this->okResource(new BrandTextResource($result['data']));
		} catch (\Throwable $e) {
			return $this->statusFail(
				['message' => 'Failed to generate brand text.', 'error' => $e->getMessage()],
				500
			);
		}
	}

	public function history(): JsonResponse
	{
		$keyword = (string) request()->query('q', '');
		$items = $this->brandTextService->history(auth('api')->id(), $keyword);

		return $this->okResource(BrandTextHistoryResource::collection($items));
	}

	public function edit(EditBrandTextRequest $request): JsonResponse
	{
		$result = $this->brandTextService->edit(
			(int) $request->input('chat_id'),
			(string) $request->input('comment'),
			$request->input('language'),
			auth('api')->id()
		);

		if ($result === null) {
			return $this->statusFail('Chat not found', 404);
		}

		return $this->okResource(new BrandTextResource($result['data']));
	}

	public function checkDomains(CheckDomainRequest $request): JsonResponse
	{
		$results = $this->brandTextService->checkDomains(
			(string) $request->input('name'),
			(array) $request->input('tlds', [])
		);

		return $this->statusOk([
			'data' => [
				'results' => DomainCheckResultResource::collection(collect($results)),
			],
		]);
	}

	public function reserveDomain(ReserveDomainRequest $request): JsonResponse
	{
		$result = $this->brandTextService->reserveDomain(
			[
				'domain' => (string) $request->input('domain'),
				'years' => (int) $request->input('years', 1),
				'whois_guard' => (bool) $request->input('whois_guard', false),
				'registrant' => (array) $request->input('registrant'),
			],
			auth('api')->id()
		);

		if (($result['success'] ?? false) === true) {
			return $this->statusOk([
				'data' => new DomainReservationResource($result),
				'message' => $result['message'] ?? 'Domain reserved successfully',
			]);
		}

		return $this->statusFail([
			'reservation_id' => $result['reservation_id'],
			'status' => $result['status'],
			'error' => $result['error'] ?? null,
		], 400);
	}
}
