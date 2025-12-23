<?php

namespace App\Http\Requests\Mobile;

use App\Http\Requests\ResponseShape as FormRequest;

class UpdatePasswordRequest extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            "password" => "required|confirmed"
        ];
    }
}
