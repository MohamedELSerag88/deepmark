<?php

return [
	'secret' => env('STRIPE_SECRET'),
	'public' => env('STRIPE_PUBLIC'),
	'currency' => env('STRIPE_CURRENCY', 'usd'),
	'success_url' => env('STRIPE_SUCCESS_URL', 'http://localhost/success'),
	'cancel_url' => env('STRIPE_CANCEL_URL', 'http://localhost/cancel'),
	'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
];


