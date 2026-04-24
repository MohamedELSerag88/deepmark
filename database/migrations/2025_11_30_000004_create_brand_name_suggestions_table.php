<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('brand_name_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_chat_id')->constrained('brand_chats')->cascadeOnDelete();
            $table->unsignedInteger('suggestion_index')->default(1);
            $table->string('name');
            $table->string('archetype')->nullable();
            $table->json('domains')->nullable();
            $table->boolean('liked')->default(false);
            $table->timestamps();

            $table->index(['brand_chat_id', 'suggestion_index']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand_name_suggestions');
    }
};
