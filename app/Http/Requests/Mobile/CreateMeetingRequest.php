<?php

namespace App\Http\Requests\Mobile;

use App\Http\Requests\ResponseShape as FormRequest;

class CreateMeetingRequest extends FormRequest
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
			'brand_id' => 'required|integer|exists:brand_chats,id',
			'meeting_at' => 'required_without_all:date,time|date|after:now',
			'date' => 'required_without:meeting_at|date_format:Y-m-d',
			'time' => 'required_without:meeting_at|date_format:H:i',
			'notes' => 'sometimes|string|max:1000',
		];
	}
}


