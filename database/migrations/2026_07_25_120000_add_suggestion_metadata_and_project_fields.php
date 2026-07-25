<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::table('brand_name_suggestions', function (Blueprint $table) {
			$table->string('name_type')->nullable()->after('archetype');
			$table->string('linguistic_style')->nullable()->after('name_type');
			$table->string('generation_technique')->nullable()->after('linguistic_style');
			$table->unsignedSmallInteger('name_length')->nullable()->after('generation_technique');
			$table->text('rationale')->nullable()->after('name_length');
			$table->text('description')->nullable()->after('rationale');
			$table->json('brand_keywords')->nullable()->after('description');
			$table->text('why_fits')->nullable()->after('brand_keywords');
		});

		Schema::table('brand_chats', function (Blueprint $table) {
			$table->string('project_name')->nullable()->after('topic');
			$table->string('selected_brand_name')->nullable()->after('project_name');
			$table->boolean('branding_email_sent')->default(false)->after('device_token');
		});
	}

	public function down(): void
	{
		Schema::table('brand_name_suggestions', function (Blueprint $table) {
			$table->dropColumn([
				'name_type',
				'linguistic_style',
				'generation_technique',
				'name_length',
				'rationale',
				'description',
				'brand_keywords',
				'why_fits',
			]);
		});

		Schema::table('brand_chats', function (Blueprint $table) {
			$table->dropColumn(['project_name', 'selected_brand_name', 'branding_email_sent']);
		});
	}
};
