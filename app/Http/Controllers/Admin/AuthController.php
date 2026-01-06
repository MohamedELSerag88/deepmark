<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:4',
        ]);

        $credentials = $request->only(['email', 'password']);
        $admin = Admin::where('email', $credentials['email'])->first();
        if (!$admin) {
            return $this->response->statusFail(['message' => trans('messages.wrong_credentials')], 200);
        }

        if (!$token = auth('admin')->attempt($credentials)) {
            return $this->response->statusFail(['message' => trans('messages.wrong_credentials')], 200);
        }

        return $this->response->statusOk([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => [ // keep key 'user' for frontend compatibility
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
            ],
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return $this->response->unauthorized(['message' => 'Unauthenticated'], 401);
        }
        return $this->response->statusOk([
            'user' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
            ],
        ]);
    }
}

