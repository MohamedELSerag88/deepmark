<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name_en')->default('deepmarks');
            $table->string('brand_name_ar')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('login_cta_label_en')->default('Login');
            $table->string('login_cta_label_ar')->nullable();
            $table->string('login_cta_url')->default('/name-branding');
            $table->string('start_cta_label_en')->default('Start now');
            $table->string('start_cta_label_ar')->nullable();
            $table->string('start_cta_url')->default('/name-branding');
            $table->text('footer_tagline_en')->nullable();
            $table->text('footer_tagline_ar')->nullable();
            $table->string('footer_copyright_en')->nullable();
            $table->string('footer_copyright_ar')->nullable();
            $table->string('newsletter_placeholder_en')->nullable();
            $table->string('newsletter_placeholder_ar')->nullable();
            $table->json('social_links')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_email_label_en')->nullable();
            $table->string('contact_email_label_ar')->nullable();
            $table->string('contact_email_desc_en')->nullable();
            $table->string('contact_email_desc_ar')->nullable();
            $table->json('contact_checklist_en')->nullable();
            $table->json('contact_checklist_ar')->nullable();
            $table->string('contact_pill_en')->nullable();
            $table->string('contact_pill_ar')->nullable();
            $table->string('contact_form_label_en')->nullable();
            $table->string('contact_form_label_ar')->nullable();
            $table->string('contact_form_title_en')->nullable();
            $table->string('contact_form_title_ar')->nullable();
            $table->text('contact_form_lead_en')->nullable();
            $table->text('contact_form_lead_ar')->nullable();
            $table->string('contact_side_label_en')->nullable();
            $table->string('contact_side_label_ar')->nullable();
            $table->string('contact_side_title_en')->nullable();
            $table->string('contact_side_title_ar')->nullable();
            $table->text('contact_side_lead_en')->nullable();
            $table->text('contact_side_lead_ar')->nullable();
            $table->string('contact_response_note_en')->nullable();
            $table->string('contact_response_note_ar')->nullable();
            $table->string('blogs_pill_en')->nullable();
            $table->string('blogs_pill_ar')->nullable();
            $table->string('blogs_title_en')->nullable();
            $table->string('blogs_title_ar')->nullable();
            $table->text('blogs_subtitle_en')->nullable();
            $table->text('blogs_subtitle_ar')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
