<?php

namespace App\Http\Controllers\Mobile\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\RestPasswordRequest;
use App\Http\Resources\Mobile\LoginResource;
use App\Models\User;

class ResetPasswordController extends Controller {


    public function resetPassword(RestPasswordRequest $request){
        $data = $request->only(['reset_password', 'new_password', 'new_password_confirmation']);


        $user = User::where(['reset_password' =>$data['reset_password']])->first();

        if(!$user)
            return $this->response->statusFail( trans('messages.wrong_reset_password_code'));

        $user->password = \Hash::make($data['new_password']);
        $user->reset_password = null;
        $user->save();
        $user->token = auth('api')->attempt(['phone' => $user->phone,'password' => $request->new_password]);
        return $this->response->statusOk(["data" => new LoginResource($user),"message" => trans('messages.password_updated_successfully')]);
    }

}
