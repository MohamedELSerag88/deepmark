<?php

namespace App\Http\Controllers\Mobile\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\CheckOtpRequest;
use App\Http\Resources\Mobile\LoginResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class CheckOtpController extends Controller {


    /**
     * data [ email , otp_code]
     * @return JsonResponse|void
     */


    public function checkOtp(CheckOtpRequest $request)
    {
        $email = $request->input('email');
        $code = $request->input('otp_code');
        $checkUser = User::where('email', $email)->first();
        $otp_expire = 10; // minutes
        if (!$checkUser || !$checkUser->otp_token)
            return $this->response->statusFail(trans('messages.wrong_otp_token'));
        if (Carbon::now()->diffInMinutes($checkUser->otp_sent_at) > $otp_expire)
            return $this->response->statusFail(trans('messages.otp_expired'));
        if (!Hash::check((string)$code, $checkUser->otp_token))
            return $this->response->statusFail(trans('messages.wrong_otp_token'));

        if (!$token = auth('api')->login($checkUser)) {
            return $this->response->statusFail(trans('messages.wrong_user_data'));
        }

        $checkUser->update(['otp_token' => null]);
        $checkUser->token = $token;
        return $this->response->statusOk(['data' => new LoginResource($checkUser), "message" => trans('messages.logged_in_successfully')]);
    }




}
