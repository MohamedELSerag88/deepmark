<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AiPromptResource;
use App\Models\AiPrompt;
use App\Services\AI\PromptTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiPromptController extends Controller
{
	public function __construct(
		private readonly PromptTemplateService $prompts,
	) {
		parent::__construct();
	}

	public function index(): JsonResponse
	{
		// Ensure default prompts exist for fresh installs.
		$this->prompts->get(AiPrompt::KEY_BRAND_NAMES_GENERATE);
		$this->prompts->get(AiPrompt::KEY_BRAND_NAMES_SIMILAR);

		$prompts = AiPrompt::query()->orderBy('key')->get();

		return $this->statusOk([
			'prompts' => AiPromptResource::collection($prompts),
			'placeholders' => PromptTemplateService::PLACEHOLDER_KEYS,
		]);
	}

	public function show(string $key): JsonResponse
	{
		try {
			$prompt = $this->prompts->get($key);
		} catch (\Throwable $e) {
			return $this->notFound(['message' => 'AI prompt not found'], 404);
		}

		return $this->statusOk([
			'prompt' => new AiPromptResource($prompt),
			'placeholders' => PromptTemplateService::PLACEHOLDER_KEYS,
		]);
	}

	public function update(Request $request, string $key): JsonResponse
	{
		try {
			$prompt = $this->prompts->get($key);
		} catch (\Throwable $e) {
			return $this->notFound(['message' => 'AI prompt not found'], 404);
		}

		$validated = $request->validate([
			'name' => 'sometimes|required|string|max:255',
			'system_template' => 'sometimes|required|string',
			'user_template' => 'nullable|string',
		]);

		$prompt->fill($validated)->save();

		return $this->statusOk([
			'prompt' => new AiPromptResource($prompt->fresh()),
			'placeholders' => PromptTemplateService::PLACEHOLDER_KEYS,
		]);
	}
}
