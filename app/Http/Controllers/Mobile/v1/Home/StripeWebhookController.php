<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Services\Billing\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
	public function __construct(
		private readonly SubscriptionService $subscriptionService,
	) {
		parent::__construct();
	}

	public function handle(Request $request)
	{
		$payload = $request->getContent();
		$sig = $request->header('Stripe-Signature');
		$secret = (string) config('stripe.webhook_secret');

		try {
			$event = Webhook::constructEvent($payload, $sig, $secret);
		} catch (\Exception $e) {
			return response('Invalid signature', 400);
		}

		switch ($event->type) {
			case 'checkout.session.completed':
				$session = $event->data->object;
				$sessionId = (string) $session->id;
				$subscriptionId = isset($session->subscription) ? (string) $session->subscription : null;
				$this->subscriptionService->handleCheckoutCompleted($sessionId, $subscriptionId);
				break;
			default:
				break;
		}

		return new Response('OK', 200);
	}
}
