<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Billing\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller {


	public function status(): JsonResponse
	{
		$sub = Subscription::with('plan')
			->where('user_id', auth()->id())
			->latest('id')
			->first();
		return $this->response->statusOk([
			'data' => $sub ? [
				'status' => $sub->status,
				'plan' => [
					'id' => $sub->plan->id,
					'name' => $sub->plan->name,
					'price_cents' => $sub->plan->price_cents,
					'currency' => $sub->plan->currency,
					'interval' => $sub->plan->interval,
				],
				'started_at' => $sub->started_at,
				'ends_at' => $sub->ends_at,
			] : null
		]);
	}

	public function subscribe(Request $request, StripeService $stripe): JsonResponse
	{
		$request->validate([
			'plan_id' => 'required|integer|exists:plans,id',
		]);
		$plan = Plan::find($request->input('plan_id'));
		if ($plan->price_cents === 0) {
			$sub = Subscription::create([
				'user_id' => auth()->id(),
				'plan_id' => $plan->id,
				'status' => 'active',
				'started_at' => now(),
			]);
			return $this->response->statusOk([
				'data' => [
					'subscription_id' => $sub->id,
					'status' => $sub->status,
				],
				'message' => 'Subscribed to free plan'
			]);
		}

		$session = $stripe->createCheckoutSession(
			$plan->price_cents,
			$plan->name,
			$plan->stripe_price_id,
			auth()->user()->email
		);

		Subscription::create([
			'user_id' => auth()->id(),
			'plan_id' => $plan->id,
			'status' => 'pending',
			'stripe_session_id' => $session['id'],
		]);

		return $this->response->statusOk([
			'data' => [
				'checkout_url' => $session['url'],
				'session_id' => $session['id'],
			]
		]);
	}
}


