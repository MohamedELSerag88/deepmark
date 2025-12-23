<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('plans', function (Blueprint $table) {
			$table->id();
			$table->string('name');
			$table->text('description')->nullable();
			$table->unsignedInteger('price_cents')->default(0);
			$table->string('currency', 10)->default('usd');
			$table->string('interval')->default('month'); // month, year
			$table->string('stripe_price_id')->nullable();
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('plans');
	}
};


