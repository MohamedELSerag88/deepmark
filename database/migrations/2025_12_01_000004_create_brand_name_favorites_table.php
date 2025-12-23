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
			$table->string('name');
			$table->string('archetype')->nullable();
			$table->json('domains')->nullable();
			$table->timestamps();
			$table->unique(['user_id', 'name']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('brand_name_favorites');
	}
};


