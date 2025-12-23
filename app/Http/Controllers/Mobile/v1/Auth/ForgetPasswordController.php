<?php

namespace App\Http\Controllers\Mobile\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\ForgetPasswordRequest;
use App\Jobs\SendMail;
use App\Models\User;

class ForgetPasswordController extends Controller {


    public function forgetPassword(ForgetPasswordRequest $request)
    {
        $email = $request->get('phone');

        $user = User::where(['phone' => $email])->first();

        return $this->sendRestPasswordEmail($user);
    }



    public function sendRestPasswordEmail($user){
        $reset_password = rand(pow(10, 3), pow(10, 4)-1);
        $user->reset_password = $reset_password;
        $user->save();

        return $this->response->statusOk(["message" => trans('messages.sms_sent_with_otp'),"token"=>$reset_password]);
    }


}
