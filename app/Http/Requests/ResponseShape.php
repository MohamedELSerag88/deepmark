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
        if (request()->wantsJson() || request()->is('api/*')) {
            $errors = $validator->errors();
            $message = $errors->first();
            $response = $this->response->statusFail(['message' => $message, 'errors' => $errors->toArray()], 422);
            throw new \Illuminate\Validation\ValidationException($validator, $response);
        }
        throw (new ValidationException($validator))->errorBag($this->errorBag);
    }

    public function getModelId($id = 4)
    {
        return $this->segment($id);
    }
}
