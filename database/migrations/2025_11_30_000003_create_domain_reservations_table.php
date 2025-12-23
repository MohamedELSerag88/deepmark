<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('domain_reservations', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained()->cascadeOnDelete();
			$table->string('domain');
			$table->unsignedTinyInteger('years')->default(1);
			$table->json('registrant');
			$table->string('provider')->default('namecheap');
			$table->string('provider_order_id')->nullable();
			$table->string('status')->default('pending'); // pending, success, failed
			$table->json('response')->nullable();
			$table->text('error')->nullable();
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('domain_reservations');
	}
};


