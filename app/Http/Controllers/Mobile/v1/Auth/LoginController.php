<?php

namespace App\Http\Controllers\Mobile\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\LoginRequest;
use App\Http\Resources\Mobile\LoginResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller {


    /**
     * @param LoginRequest $request
     * @data [ phone , password]
     * @return JsonResponse
     */


    public function login(LoginRequest $request)
    {
        $credentials = $request->only(['phone', 'password']);

        $user = User::where(['phone' =>$credentials['phone']])->first();

        if(!$user)
            return $this->response->statusFail(trans('messages.user_not_found'));


        if (!$token = auth('api')->attempt($credentials)) {
            return $this->response->statusFail(['message' => trans('messages.wrong_credentials')]);
        }
        $user->token = $token;
        $data = ['data' => new LoginResource($user), "message" => trans('messages.user_founded_successfully')];
        return $this->response->statusOk($data);
    }


}
