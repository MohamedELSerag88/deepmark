<?php

namespace App\Http\Requests\Mobile;

use App\Http\Requests\ResponseShape as FormRequest;

class CreateBrandNamesRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'answers' => 'required|array|min:1',
			'answers.*.question_id' => 'required|integer|exists:questions,id',
			'answers.*.value' => 'required',
			'language' => 'nullable|in:en,ar',
			'count' => 'nullable|integer|min:3|max:40',
			'tlds' => 'nullable|array',
			'tlds.*' => 'string',
		];
	}
}


