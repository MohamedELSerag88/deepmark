<?php

namespace App\Http\Requests\Mobile;

use App\Http\Requests\ResponseShape as FormRequest;

class CreateBrandTextRequest extends FormRequest
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
			'answers' => 'required|array|min:1',
			'answers.*.question_id' => 'required|integer|exists:questions,id',
			'answers.*.value' => 'required', // can be string or array (for multi_choice)
			'language' => 'sometimes|in:en,ar,both',
		];
	}
}


