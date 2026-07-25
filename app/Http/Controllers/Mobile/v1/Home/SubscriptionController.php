<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\SubscribeRequest;
use App\Http\Resources\Mobile\MessageResource;
use App\Http\Resources\Mobile\SubscriptionStatusResource;
use App\Services\Billing\SubscriptionService;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
	public function __construct(
		private readonly SubscriptionService $subscriptionService,
	) {
		parent::__construct();
	}

	public function status(): JsonResponse
	{
		$status = $this->subscriptionService->status(auth('api')->id());

		return $this->statusOk([
			'data' => $status === null ? null : new SubscriptionStatusResource($status),
		]);
	}

	public function subscribe(SubscribeRequest $request): JsonResponse
	{
		try {
			$result = $this->subscriptionService->subscribe(
				(int) $request->input('plan_id'),
				auth()->user()
			);

			if (($result['type'] ?? null) === 'free') {
				return $this->statusOk([
					'data' => new MessageResource([
						'subscription_id' => $result['subscription_id'],
						'status' => $result['status'],
					]),
					'message' => $result['message'] ?? 'Subscribed to free plan',
				]);
			}

			return $this->okResource(new MessageResource([
				'checkout_url' => $result['checkout_url'],
				'session_id' => $result['session_id'],
			]));
		} catch (\Throwable $e) {
			return $this->statusFail(
				['message' => 'Subscription failed.', 'error' => $e->getMessage()],
				500
			);
		}
	}
}
