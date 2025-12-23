<?php

namespace App\Http\Requests\Mobile;

use App\Http\Requests\ResponseShape as FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "name" => "required|string|min:2",
            "email" => "required|email|unique:users,email",
            "phone" => "required|unique:users,phone",
            "password" => "required|string|min:8",
        ];
    }
}
