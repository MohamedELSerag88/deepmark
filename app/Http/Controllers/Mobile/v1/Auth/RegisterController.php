<?php

namespace App\Http\Controllers\Mobile\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\RegisterRequest;
use App\Http\Resources\Mobile\LoginResource;
use App\Models\User;

class RegisterController extends Controller {


    public function register(RegisterRequest $request){
        $data = $request->validated(); // name, email, phone, password
        $fullName = trim($data['name']);
        $nameParts = preg_split('/\s+/', $fullName, 2);
        $firstName = $nameParts[0] ?? null;
        $lastName = $nameParts[1] ?? null;

        $user = User::create([
            'fname' => $firstName,
            'lname' => $lastName,
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'], // will be hashed by model cast
        ]);
        $user->token = auth('api')->login($user);

        return $this->response->statusOk(["data" => new LoginResource($user),"message" => trans('messages.user_created_successfully')]);
    }

}
