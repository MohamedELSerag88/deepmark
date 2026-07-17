<?php

namespace App\Http\Requests\Mobile\Marketing;

use App\Http\Requests\ResponseShape;

class StoreContactSubmissionRequest extends ResponseShape
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'brand' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'budget' => 'nullable|string|max:100',
            'timeline' => 'nullable|string|max:100',
        ];
    }
}
