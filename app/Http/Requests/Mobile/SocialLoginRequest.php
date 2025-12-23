<?php

namespace App\Http\Requests\Mobile;

use Illuminate\Foundation\Http\FormRequest;

class SocialLoginRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'provider' => 'required|string|in:google,facebook,apple',
			'token' => 'required|string',
			'email' => 'nullable|email',
			'fname' => 'nullable|string|max:255',
			'lname' => 'nullable|string|max:255',
		];
	}
}


