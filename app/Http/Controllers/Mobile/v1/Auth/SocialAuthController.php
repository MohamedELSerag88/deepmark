<?php

namespace App\Http\Controllers\Mobile\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\LoginResource;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use GuzzleHttp\Client;

class SocialAuthController extends Controller
{
    /**
     * Get redirect URL for provider
     */
    public function redirectToProvider($provider)
    {
        $this->validateProvider($provider);

        return response()->json([
            'redirect_url' => Socialite::driver($provider)
                ->stateless()
                ->redirect()
                ->getTargetUrl()
        ]);
    }

    /**
     * Handle provider callback
     */
    public function handleProviderCallback(Request $request, $provider)
    {
        $this->validateProvider($provider);

        try {
            $httpClient = new Client([
                'verify' => base_path('cacert.pem'), // put cacert.pem in project root or another known path
            ]);
            $socialUser = Socialite::driver($provider)
                ->setHttpClient($httpClient)
                ->stateless()
                ->user();

            $user = User::updateOrCreateSocialUser($provider, $socialUser);

            // Create API token
            if (!$token = auth('api')->login($user)) {
                return $this->response->statusFail(['message' => trans('messages.wrong_credentials')]);
            }
            $user->token = $token;
            $data = ['data' => new LoginResource($user), "message" => trans('messages.user_founded_successfully')];
            return $this->response->statusOk($data);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication failed: ' . $e->getMessage()
            ], 401);
        }
    }

    /**
     * Alternative: Exchange token for existing social token
     * For mobile apps that already have the social token
     */
    public function exchangeSocialToken(Request $request, $provider)
    {
        $request->validate([
            'access_token' => 'required|string'
        ]);

        $this->validateProvider($provider);

        try {
            $socialUser = Socialite::driver($provider)
                ->stateless()
                ->userFromToken($request->access_token);

            $user = User::updateOrCreateSocialUser($provider, $socialUser);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication failed: ' . $e->getMessage()
            ], 401);
        }
    }

    private function validateProvider($provider)
    {
        if (!in_array($provider, ['google', 'facebook', 'apple'])) {
            abort(400, 'Invalid provider');
        }
    }
}
