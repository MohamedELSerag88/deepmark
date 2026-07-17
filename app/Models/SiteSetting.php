<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'brand_name_en',
        'brand_name_ar',
        'logo_url',
        'login_cta_label_en',
        'login_cta_label_ar',
        'login_cta_url',
        'start_cta_label_en',
        'start_cta_label_ar',
        'start_cta_url',
        'footer_tagline_en',
        'footer_tagline_ar',
        'footer_copyright_en',
        'footer_copyright_ar',
        'newsletter_placeholder_en',
        'newsletter_placeholder_ar',
        'social_links',
        'contact_email',
        'contact_email_label_en',
        'contact_email_label_ar',
        'contact_email_desc_en',
        'contact_email_desc_ar',
        'contact_checklist_en',
        'contact_checklist_ar',
        'contact_pill_en',
        'contact_pill_ar',
        'contact_form_label_en',
        'contact_form_label_ar',
        'contact_form_title_en',
        'contact_form_title_ar',
        'contact_form_lead_en',
        'contact_form_lead_ar',
        'contact_side_label_en',
        'contact_side_label_ar',
        'contact_side_title_en',
        'contact_side_title_ar',
        'contact_side_lead_en',
        'contact_side_lead_ar',
        'contact_response_note_en',
        'contact_response_note_ar',
        'blogs_pill_en',
        'blogs_pill_ar',
        'blogs_title_en',
        'blogs_title_ar',
        'blogs_subtitle_en',
        'blogs_subtitle_ar',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'contact_checklist_en' => 'array',
            'contact_checklist_ar' => 'array',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'brand_name_en' => 'deepmarks',
        ]);
    }
}
