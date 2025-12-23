<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\QuestionResource;
use App\Models\Question;
use Illuminate\Http\JsonResponse;

class QuestionController extends Controller {


	public function index(): JsonResponse
	{
		$questions = Question::query()->latest()->get();
		return $this->response->statusOk([
			'data' => QuestionResource::collection($questions)
		]);
	}
}


