<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('questions', function (Blueprint $table) {
			$table->text('description_en')->nullable()->after('question_ar');
			$table->text('description_ar')->nullable()->after('description_en');
			$table->string('video_url')->nullable()->after('description_ar');
			$table->string('video_path')->nullable()->after('video_url');
			$table->string('image_url')->nullable()->after('video_path');
			$table->text('example_answer')->nullable()->after('image_url');
			$table->json('resources')->nullable()->after('example_answer');
			$table->string('question_type')->default('text')->change();

			// Keep existing question_type; all current usage treats it as 'text' for now
			// If you need to enforce default('text'), add doctrine/dbal and enable ->change().
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('questions', function (Blueprint $table) {
			$table->dropColumn([
				'description_en',
				'description_ar',
				'video_url',
				'video_path',
				'image_url',
				'example_answer',
				'resources',
			]);
		});
	}
};


