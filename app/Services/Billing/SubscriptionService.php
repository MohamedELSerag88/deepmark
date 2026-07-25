<?php

namespace App\Services\Billing;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

class SubscriptionService
{
	public function __construct(
		private readonly StripeService $stripe,
	) {}

	public function status(?int $userId): ?array
	{
		$sub = Subscription::with('plan')
			->where('user_id', $userId)
			->latest('id')
			->first();

		if (!$sub) {
			return null;
		}

		return [
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
		];
	}

	/**
	 * @return array{type: string, subscription_id?: int, status?: string, message?: string, checkout_url?: string, session_id?: string}
	 */
	public function subscribe(int $planId, User $user): array
	{
		$plan = Plan::find($planId);
		if ($plan->price_cents === 0) {
			$sub = Subscription::create([
				'user_id' => $user->id,
				'plan_id' => $plan->id,
				'status' => 'active',
				'started_at' => now(),
			]);

			return [
				'type' => 'free',
				'subscription_id' => $sub->id,
				'status' => $sub->status,
				'message' => 'Subscribed to free plan',
			];
		}

		$session = $this->stripe->createCheckoutSession(
			$plan->price_cents,
			$plan->name,
			$plan->stripe_price_id,
			$user->email
		);

		Subscription::create([
			'user_id' => $user->id,
			'plan_id' => $plan->id,
			'status' => 'pending',
			'stripe_session_id' => $session['id'],
		]);

		return [
			'type' => 'checkout',
			'checkout_url' => $session['url'],
			'session_id' => $session['id'],
		];
	}

	public function handleCheckoutCompleted(string $sessionId, ?string $stripeSubscriptionId): void
	{
		$sub = Subscription::where('stripe_session_id', $sessionId)->first();
		if ($sub) {
			$sub->update([
				'status' => 'active',
				'started_at' => now(),
				'stripe_subscription_id' => $stripeSubscriptionId,
			]);
		}
	}
}
