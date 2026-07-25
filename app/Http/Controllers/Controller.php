<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Traits\ApiPaginator;
use App\Http\Traits\ApiResponse;
use Illuminate\Support\Facades\App;

abstract class Controller
{
	use ApiPaginator;
	use ApiResponse;

	public function __construct()
	{
		$locale = request()->header('lang', 'en');
		if (in_array($locale, config('app.locales', ['en', 'ar']), true)) {
			App::setLocale($locale);
		}
	}

	protected function respondWithCollection($collection): mixed
	{
		$data = forward_static_call([$this->modelResource, 'collection'], $collection);
		$data = $this->getPaginatedResponse($collection, $data);

		return $this->statusOk($data);
	}
}
