<?php

namespace App\Services\Billing;

use Stripe\StripeClient;

class StripeService
{
	private StripeClient $client;
	private string $currency;
	private string $successUrl;
	private string $cancelUrl;

	public function __construct()
	{
		$this->client = new StripeClient((string)config('stripe.secret'));
		$this->currency = (string)config('stripe.currency', 'usd');
		$this->successUrl = (string)config('stripe.success_url');
		$this->cancelUrl = (string)config('stripe.cancel_url');
	}

	public function createCheckoutSession(int $amountCents, string $name, ?string $priceId = null, ?string $customerEmail = null): array
	{
		$params = [
			'mode' => 'subscription',
			'success_url' => $this->successUrl . '?session_id={CHECKOUT_SESSION_ID}',
			'cancel_url' => $this->cancelUrl,
		];

		if ($customerEmail) {
			$params['customer_email'] = $customerEmail;
		}

		if ($priceId) {
			$params['line_items'] = [[
				'price' => $priceId,
				'quantity' => 1,
			]];
		} else {
			$params['line_items'] = [[
				'price_data' => [
					'currency' => $this->currency,
					'product_data' => ['name' => $name],
					'recurring' => ['interval' => 'month'],
					'unit_amount' => $amountCents,
				],
				'quantity' => 1,
			]];
		}

		$session = $this->client->checkout->sessions->create($params);
		return [
			'id' => $session->id,
			'url' => $session->url,
		];
	}
}


