<?php

namespace Database\Seeders;

use App\Models\AiPrompt;
use App\Services\AI\PromptTemplateService;
use Illuminate\Database\Seeder;

class AiPromptSeeder extends Seeder
{
	public function run(): void
	{
		$templates = app(PromptTemplateService::class);

		AiPrompt::updateOrCreate(
			['key' => AiPrompt::KEY_BRAND_NAMES_GENERATE],
			[
				'name' => 'Main Name Generation Prompt',
				'system_template' => $templates->defaultBrandNamesSystemTemplate(),
				'user_template' => $templates->defaultBrandNamesUserTemplate(),
			]
		);

		AiPrompt::updateOrCreate(
			['key' => AiPrompt::KEY_BRAND_NAMES_SIMILAR],
			[
				'name' => 'Similar Names Generation Prompt',
				'system_template' => $templates->defaultSimilarNamesSystemTemplate(),
				'user_template' => $templates->defaultSimilarNamesUserTemplate(),
			]
		);
	}
}
