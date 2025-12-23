<?php

namespace App\Http\Requests\Mobile;

use App\Http\Requests\ResponseShape as FormRequest;

class ShareBrandNamesRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'emails' => 'required|array|min:1',
			'emails.*' => 'required|email',
			'subject' => 'nullable|string|max:180',
			'message' => 'nullable|string|max:2000',
			'brand_chat_id' => 'nullable|integer|exists:brand_chats,id',
			'names' => 'nullable|array|min:1',
			'names.*.name' => 'required_with:names|string|max:100',
			'names.*.archetype' => 'nullable|string|max:100',
			'names.*.domains' => 'nullable|array',
		];
	}
}


