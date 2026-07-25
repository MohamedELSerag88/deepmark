<?php

namespace App\Services\Auth;

use App\Models\User;
use Symfony\Component\HttpClient\HttpClient;

class SocialAuthService
{
	/**
	 * @param  array{provider: string, token: string, email?: string|null, fname?: string|null, lname?: string|null}  $data
	 * @return array{ok: bool, user?: User, error?: string, status?: int}
	 */
	public function login(array $data): array
	{
		$provider = $data['provider'];
		$token = $data['token'];

		$profile = $this->fetchProviderProfile($provider, $token);
		if (!$profile) {
			return ['ok' => false, 'error' => 'Invalid social token', 'status' => 401];
		}

		$email = $profile['email'] ?? ($data['email'] ?? null);
		if (!$email) {
			return ['ok' => false, 'error' => 'Email is required from provider or payload', 'status' => 422];
		}

		$user = User::firstOrNew(['email' => $email]);
		if (!$user->exists) {
			$user->fname = ($data['fname'] ?? null) ?? ($profile['given_name'] ?? ($profile['name'] ?? null));
			$user->lname = ($data['lname'] ?? null) ?? ($profile['family_name'] ?? null);
		}
		$user->provider = $provider;
		$user->provider_id = $profile['id'] ?? $profile['sub'] ?? null;
		$user->avatar = $profile['picture'] ?? null;
		$user->save();

		if (!$tokenJwt = auth('api')->login($user)) {
			return ['ok' => false, 'error' => 'Unable to login user'];
		}
		$user->token = $tokenJwt;

		return ['ok' => true, 'user' => $user];
	}

	public function fetchProviderProfile(string $provider, string $token): ?array
	{
		$client = HttpClient::create();
		try {
			if ($provider === 'google') {
				$response = $client->request('GET', 'https://oauth2.googleapis.com/tokeninfo', [
					'query' => ['id_token' => $token],
					'max_redirects' => 1,
					'timeout' => 10,
				]);
				if ($response->getStatusCode() === 200) {
					$data = $response->toArray(false);

					return [
						'id' => $data['sub'] ?? null,
						'email' => $data['email'] ?? null,
						'given_name' => $data['given_name'] ?? null,
						'family_name' => $data['family_name'] ?? null,
						'picture' => $data['picture'] ?? null,
						'sub' => $data['sub'] ?? null,
					];
				}
			} elseif ($provider === 'facebook') {
				$response = $client->request('GET', 'https://graph.facebook.com/me', [
					'query' => [
						'access_token' => $token,
						'fields' => 'id,name,email,picture.type(large)',
					],
					'max_redirects' => 1,
					'timeout' => 10,
				]);
				if ($response->getStatusCode() === 200) {
					$data = $response->toArray(false);

					return [
						'id' => $data['id'] ?? null,
						'email' => $data['email'] ?? null,
						'name' => $data['name'] ?? null,
						'picture' => $data['picture']['data']['url'] ?? null,
					];
				}
			} elseif ($provider === 'apple') {
				$segments = explode('.', $token);
				if (count($segments) >= 2) {
					$payload = json_decode($this->base64UrlDecode($segments[1]), true) ?: [];
					if (($payload['iss'] ?? '') === 'https://appleid.apple.com') {
						return [
							'sub' => $payload['sub'] ?? null,
							'email' => $payload['email'] ?? null,
						];
					}
				}
			}
		} catch (\Throwable $e) {
			return null;
		}

		return null;
	}

	private function base64UrlDecode(string $data): string
	{
		$remainder = strlen($data) % 4;
		if ($remainder) {
			$data .= str_repeat('=', 4 - $remainder);
		}

		return base64_decode(strtr($data, '-_', '+/')) ?: '';
	}
}
