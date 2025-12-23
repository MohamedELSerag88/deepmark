<?php

namespace App\Services\AI;

use OpenAI\Client;

class DeepSeekService
{
	private Client $client;
	private string $defaultModel;

	public function __construct(Client $client, string $defaultModel)
	{
		$this->client = $client;
		$this->defaultModel = $defaultModel;
	}

	public function chat(array $messages, ?string $model = null): string
	{
		$response = $this->client->chat()->create([
			'model' => $model ?: $this->defaultModel,
			'messages' => $messages,
			'stream' => false,
		]);

		return $response->choices[0]->message->content ?? '';
	}

	public function simpleChat(string $prompt, ?string $system = null, ?string $model = null): string
	{
		$messages = [];
		if ($system !== null) {
			$messages[] = ['role' => 'system', 'content' => $system];
		}
		$messages[] = ['role' => 'user', 'content' => $prompt];
		return $this->chat($messages, $model);
	}
}


