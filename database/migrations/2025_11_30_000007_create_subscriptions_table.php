<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('subscriptions', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained()->cascadeOnDelete();
			$table->foreignId('plan_id')->constrained()->cascadeOnDelete();
			$table->string('status')->default('pending'); // pending, active, canceled
			$table->dateTime('started_at')->nullable();
			$table->dateTime('ends_at')->nullable();
			$table->string('stripe_session_id')->nullable();
			$table->string('stripe_subscription_id')->nullable();
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('subscriptions');
	}
};


