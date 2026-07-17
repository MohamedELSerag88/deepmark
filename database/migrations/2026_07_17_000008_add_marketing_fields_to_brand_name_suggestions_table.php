<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brand_name_suggestions', function (Blueprint $table) {
            $table->boolean('is_marketing_featured')->default(false)->after('liked');
            $table->string('marketing_image_url')->nullable()->after('is_marketing_featured');
            $table->string('marketing_author_name')->nullable();
            $table->string('marketing_author_position')->nullable();
            $table->string('marketing_author_avatar_url')->nullable();
            $table->text('marketing_description_en')->nullable();
            $table->text('marketing_description_ar')->nullable();
            $table->text('marketing_lead_en')->nullable();
            $table->text('marketing_lead_ar')->nullable();
            $table->json('marketing_gallery_images')->nullable();
            $table->json('marketing_content_en')->nullable();
            $table->json('marketing_content_ar')->nullable();
            $table->json('marketing_deliverables_en')->nullable();
            $table->json('marketing_deliverables_ar')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('brand_name_suggestions', function (Blueprint $table) {
            $table->dropColumn([
                'is_marketing_featured',
                'marketing_image_url',
                'marketing_author_name',
                'marketing_author_position',
                'marketing_author_avatar_url',
                'marketing_description_en',
                'marketing_description_ar',
                'marketing_lead_en',
                'marketing_lead_ar',
                'marketing_gallery_images',
                'marketing_content_en',
                'marketing_content_ar',
                'marketing_deliverables_en',
                'marketing_deliverables_ar',
            ]);
        });
    }
};
