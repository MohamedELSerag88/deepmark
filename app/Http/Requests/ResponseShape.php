<?php

namespace App\Http\Requests;

use App\Http\Response\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ResponseShape extends FormRequest
{
    public $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }
    protected function failedValidation(Validator $validator)
    {
        if (request()->wantsJson()) {
            $response = $this->response->statusFail($validator->errors()->first(), 422);
            throw new \Illuminate\Validation\ValidationException($validator, $response);
        } else {
            throw (new ValidationException($validator))->errorBag($this->errorBag);
        }
    }

    public function getModelId($id = 4)
    {
        return $this->segment($id);
    }
}
