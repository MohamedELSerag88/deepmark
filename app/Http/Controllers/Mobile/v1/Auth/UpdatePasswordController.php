<?php

namespace App\Http\Controllers\Mobile\v1\Auth;


use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\UpdatePasswordRequest;

class UpdatePasswordController extends Controller {

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $password = $request->get('password');
        $user = auth('client')->user();
        $user->password = \Hash::make($password);
        $user->save();
        return $this->response->statusOk(trans('messages.password_updated_successfully'));

    }







}
