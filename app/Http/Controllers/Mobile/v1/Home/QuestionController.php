<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\QuestionResource;
use App\Services\Question\QuestionService;
use Illuminate\Http\JsonResponse;

class QuestionController extends Controller
{
	public function __construct(
		private readonly QuestionService $questionService,
	) {
		parent::__construct();
	}

	public function index(): JsonResponse
	{
		return $this->okResource(
			QuestionResource::collection($this->questionService->list())
		);
	}
}
