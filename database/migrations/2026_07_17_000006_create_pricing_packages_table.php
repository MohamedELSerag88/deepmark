<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_packages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_en');
            $table->string('name_ar')->nullable();
            $table->string('price_display')->nullable();
            $table->string('currency_symbol')->default('$');
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->json('features_en')->nullable();
            $table->json('features_ar')->nullable();
            $table->string('badge_en')->nullable();
            $table->string('badge_ar')->nullable();
            $table->boolean('is_recommended')->default(false);
            $table->string('cta_label_en')->default('Start Now');
            $table->string('cta_label_ar')->nullable();
            $table->string('cta_url')->default('/contact');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_packages');
    }
};
