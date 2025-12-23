<?php

namespace App\Http\Requests\Mobile;

use App\Http\Requests\ResponseShape as FormRequest;

class SaveFavoriteNameRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'name' => 'required|string|max:100',
			'archetype' => 'nullable|string|max:100',
			'domains' => 'nullable|array',
			'brand_chat_id' => 'nullable|integer|exists:brand_chats,id',
		];
	}
}


