<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\ProjectResource;
use App\Services\Brand\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
	public function __construct(
		private readonly ProjectService $projectService,
	) {
		parent::__construct();
	}

	public function index(Request $request): JsonResponse
	{
		$result = $this->projectService->index(
			auth('api')->id(),
			$request->query('topic'),
			(int) $request->query('per_page', 10)
		);

		return $this->statusOk([
			'projects' => ProjectResource::collection($result['projects']),
			'pagination' => $result['pagination'],
		]);
	}

	public function show(int|string $id): JsonResponse
	{
		$project = $this->projectService->show(
			(int) $id,
			auth('api')->id(),
			[
				'name' => (string) request()->query('name', ''),
				'archetype' => (string) request()->query('archetype', ''),
				'name_type' => (string) request()->query('name_type', ''),
				'linguistic_style' => (string) request()->query('linguistic_style', ''),
				'generation_technique' => (string) request()->query('generation_technique', ''),
				'length' => (string) request()->query('length', ''),
			]
		);

		if ($project === null) {
			return $this->notFound(['message' => 'Project not found']);
		}

		return $this->statusOk([
			'data' => [
				'project' => new ProjectResource($project),
			],
		]);
	}

	public function selectBrandName(Request $request, int|string $id): JsonResponse
	{
		$validated = $request->validate([
			'name' => 'required|string|max:255',
		]);

		$result = $this->projectService->selectBrandName(
			(int) $id,
			(string) $validated['name'],
			auth('api')->id()
		);

		if ($result === null) {
			return $this->notFound(['message' => 'Project not found']);
		}
		if (isset($result['error']) && $result['error'] === 'suggestion_not_found') {
			return $this->statusFail(['message' => 'Brand name suggestion not found for this project'], 422);
		}

		return $this->statusOk([
			'data' => [
				'project' => new ProjectResource($result),
			],
			'message' => 'Selected brand name updated.',
		]);
	}
}
