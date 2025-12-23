<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
	public function run(): void
	{
		$currency = config('stripe.currency', 'usd');

		$plans = [
			[
				'name' => 'Basic',
				'description' => 'Starter plan for individuals',
				'price_cents' => 0,
				'currency' => $currency,
				'interval' => 'month',
				'features' => [
					['key' => 'brand_generations', 'label' => 'Brand generations / month', 'value' => '3'],
					['key' => 'domain_checks', 'label' => 'Domain checks / month', 'value' => '10'],
				],
			],
			[
				'name' => 'Pro',
				'description' => 'For growing teams',
				'price_cents' => 1999,
				'currency' => $currency,
				'interval' => 'month',
				'features' => [
					['key' => 'brand_generations', 'label' => 'Brand generations / month', 'value' => '20'],
					['key' => 'domain_checks', 'label' => 'Domain checks / month', 'value' => '100'],
					['key' => 'priority_support', 'label' => 'Priority support', 'value' => 'yes'],
				],
			],
			[
				'name' => 'Business',
				'description' => 'Advanced features and volume',
				'price_cents' => 4999,
				'currency' => $currency,
				'interval' => 'month',
				'features' => [
					['key' => 'brand_generations', 'label' => 'Brand generations / month', 'value' => 'Unlimited'],
					['key' => 'domain_checks', 'label' => 'Domain checks / month', 'value' => 'Unlimited'],
					['key' => 'priority_support', 'label' => 'Priority support', 'value' => 'yes'],
				],
			],
		];

		foreach ($plans as $data) {
			$features = $data['features'];
			unset($data['features']);
			$plan = Plan::create($data);
			foreach ($features as $f) {
				$f['plan_id'] = $plan->id;
				PlanFeature::create($f);
			}
		}
	}
}


