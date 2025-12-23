<?php

namespace App\Http\Requests\Mobile;

use App\Http\Requests\ResponseShape as FormRequest;

class ReserveDomainRequest extends FormRequest
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
			'domain' => 'required|string',
			'years' => 'sometimes|integer|min:1|max:5',
			'whois_guard' => 'sometimes|boolean',
			'registrant' => 'required|array',
			'registrant.first_name' => 'required|string',
			'registrant.last_name' => 'required|string',
			'registrant.address1' => 'required|string',
			'registrant.city' => 'required|string',
			'registrant.state_province' => 'required|string',
			'registrant.postal_code' => 'required|string',
			'registrant.country' => 'required|string|size:2',
			'registrant.phone' => 'required|string',
			'registrant.email' => 'required|email',
		];
	}
}


