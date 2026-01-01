<?php

namespace App\Http\Controllers\Mobile\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\SendOtpRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpCodeMail;

class SendOtpController extends Controller {



    public function sendOtp(SendOtpRequest $request)
    {
        $user = User::firstOrNew(['email' =>  $request->input('email')]);
        return $this->sendEmailOtp($user);

    }

    public function sendEmailOtp($user){
        try{
            $otp_code = 123456;//random_int(100000, 999999);
            $user->otp_token = Hash::make((string)$otp_code);
            $user->otp_sent_at = Carbon::now();
            if (!$user->exists) {
                $user->password = null;
            }
            $user->save();
            Mail::to($user->email)->send(new OtpCodeMail($otp_code));
            return $this->response->statusOk(["message" => trans('messages.sms_sent_with_otp')]);
        }
        catch (\Exception $exception){
            return $this->response->statusFail($exception->getMessage(), 500);
        }

    }


}
