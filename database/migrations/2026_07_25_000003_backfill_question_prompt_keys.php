<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
	public function up(): void
	{
		$mappings = [
			'%main point%' => 'business_description',
			'%primary audience%' => 'target_audience',
			'%transformation should customers feel%' => 'differentiator',
			'%voice and tone%' => 'preferred_tone',
			'%top competitors%' => 'competitors',
			'%visual attributes%' => 'main_feel',
		];

		foreach ($mappings as $like => $promptKey) {
			DB::table('questions')
				->whereNull('prompt_key')
				->where('question_en', 'like', $like)
				->update(['prompt_key' => $promptKey]);
		}
	}

	public function down(): void
	{
		// Non-destructive: leave prompt_key values in place.
	}
};
