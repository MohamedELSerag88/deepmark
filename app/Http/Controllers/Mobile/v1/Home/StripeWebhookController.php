<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Webhook;

class StripeWebhookController extends Controller {


	public function handle(Request $request)
	{
		$payload = $request->getContent();
		$sig = $request->header('Stripe-Signature');
		$secret = (string)config('stripe.webhook_secret');

		try {
			$event = Webhook::constructEvent($payload, $sig, $secret);
		} catch (\Exception $e) {
			return response('Invalid signature', 400);
		}

		switch ($event->type) {
			case 'checkout.session.completed':
				$session = $event->data->object;
				$sessionId = (string)$session->id;
				$subscriptionId = isset($session->subscription) ? (string)$session->subscription : null;
				$sub = Subscription::where('stripe_session_id', $sessionId)->first();
				if ($sub) {
					$sub->update([
						'status' => 'active',
						'started_at' => now(),
						'stripe_subscription_id' => $subscriptionId,
					]);
				}
				break;
			default:
				break;
		}

		return new Response('OK', 200);
	}
}


