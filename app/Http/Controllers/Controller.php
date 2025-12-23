<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Traits\ApiPaginator;
use App\Http\Response\Response;
use Illuminate\Support\Facades\App;

abstract class Controller
{
    use ApiPaginator;
    public function __construct(Response $response)
    {
        $locale = request()->header('lang', 'en');
        if (in_array($locale, config('app.locales')))
            App::setLocale($locale);


        $this->response = $response;
    }

    protected function respondWithCollection($collection): mixed
    {
        $data = forward_static_call([$this->modelResource, 'collection'], $collection);
        $data = $this->getPaginatedResponse($collection, $data);
        return $this->response->statusOk($data);
    }
}
