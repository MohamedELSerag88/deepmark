<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->date('published_at')->nullable();
            $table->string('title_en');
            $table->string('title_ar')->nullable();
            $table->string('badge_en')->nullable();
            $table->string('badge_ar')->nullable();
            $table->string('image_url')->nullable();
            $table->string('author_name')->nullable();
            $table->string('author_title_en')->nullable();
            $table->string('author_title_ar')->nullable();
            $table->string('author_avatar_url')->nullable();
            $table->text('lead_en')->nullable();
            $table->text('lead_ar')->nullable();
            $table->json('content_en')->nullable();
            $table->json('content_ar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
