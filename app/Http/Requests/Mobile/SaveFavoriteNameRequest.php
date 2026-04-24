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
			'project_id' => 'required|integer|exists:brand_chats,id',
			'suggestion_id' => 'required|integer|exists:brand_name_suggestions,id',
		];
	}
}


