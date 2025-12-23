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
		Schema::create('brand_chats', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained()->cascadeOnDelete();
			$table->string('language')->default('both');
			$table->json('answers');
			$table->json('response')->nullable();
			$table->longText('raw_response')->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('brand_chats');
	}
};


