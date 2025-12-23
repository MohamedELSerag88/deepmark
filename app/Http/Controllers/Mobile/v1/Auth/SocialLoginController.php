<?php

namespace App\Http\Controllers\Mobile\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\SocialLoginRequest;
use App\Http\Resources\Mobile\LoginResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpClient\HttpClient;

class SocialLoginController extends Controller
{
	/**
	 * Exchange a social OAuth token for our JWT.
	 * Accepts providers: google, facebook, apple.
	 */
	public function login(SocialLoginRequest $request): JsonResponse
	{
		$provider = $request->input('provider');
		$token = $request->input('token');

		$profile = $this->fetchProviderProfile($provider, $token);
		if (!$profile) {
			return $this->response->statusFail('Invalid social token', 401);
		}

		$email = $profile['email'] ?? $request->input('email');
		if (!$email) {
			return $this->response->statusFail('Email is required from provider or payload', 422);
		}

		$user = User::firstOrNew(['email' => $email]);
		if (!$user->exists) {
			$user->fname = $request->input('fname') ?? ($profile['given_name'] ?? ($profile['name'] ?? null));
			$user->lname = $request->input('lname') ?? ($profile['family_name'] ?? null);
		}
		$user->provider = $provider;
		$user->provider_id = $profile['id'] ?? $profile['sub'] ?? null;
		$user->avatar = $profile['picture'] ?? null;
		$user->save();

		if (!$tokenJwt = auth('api')->login($user)) {
			return $this->response->statusFail('Unable to login user');
		}
		$user->token = $tokenJwt;
		return $this->response->statusOk(['data' => new LoginResource($user), 'message' => trans('messages.logged_in_successfully')]);
	}

	private function fetchProviderProfile(string $provider, string $token): ?array
	{
		$client = HttpClient::create();
		try {
			if ($provider === 'google') {
				// Prefer id_token verification
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
				// identity token (JWT); decode without verification and minimally validate issuer
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


