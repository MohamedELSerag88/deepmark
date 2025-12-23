<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::table('brand_chats', function (Blueprint $table) {
			$table->string('topic')->default('brand_text')->after('user_id'); // brand_text, brand_names
		});
	}

	public function down(): void
	{
		Schema::table('brand_chats', function (Blueprint $table) {
			$table->dropColumn('topic');
		});
	}
};


