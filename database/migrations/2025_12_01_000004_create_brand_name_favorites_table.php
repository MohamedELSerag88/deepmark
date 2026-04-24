<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('brand_name_favorites', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained()->cascadeOnDelete();
			$table->foreignId('brand_chat_id')->nullable()->constrained('brand_chats')->nullOnDelete();
			$table->foreignId('brand_name_suggestion_id')->nullable()->constrained('brand_name_suggestions')->nullOnDelete();
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('brand_name_favorites');
	}
};


