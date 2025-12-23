<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Services\AI\DeepSeekService;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
		$this->app->singleton(DeepSeekService::class, function ($app) {
			$config = $app['config']->get('deepseek', []);
			$apiKey = (string)($config['api_key'] ?? '');
			$baseUrl = rtrim((string)($config['base_url'] ?? 'https://api.deepseek.com'), '/');
			$defaultModel = (string)($config['model'] ?? 'deepseek-chat');

			$client = \OpenAI::factory()
				->withApiKey($apiKey)
				->withBaseUri($baseUrl)
				->make();

			return new DeepSeekService($client, $defaultModel);
		});
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Schema::defaultStringLength(191);
    }
}
