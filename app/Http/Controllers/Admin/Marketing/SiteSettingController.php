<?php

namespace App\Http\Controllers\Admin\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Resources\JsonDataResource;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteSettingController extends Controller
{
    public function show(): JsonResponse
    {
        return $this->statusOk([
            'settings' => new JsonDataResource(SiteSetting::current()),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $settings = SiteSetting::current();

        $validated = $request->validate([
            'brand_name_en' => 'sometimes|required|string|max:255',
            'brand_name_ar' => 'nullable|string|max:255',
            'logo_url' => 'nullable|string|max:2000',
            'login_cta_label_en' => 'nullable|string|max:255',
            'login_cta_label_ar' => 'nullable|string|max:255',
            'login_cta_url' => 'nullable|string|max:500',
            'start_cta_label_en' => 'nullable|string|max:255',
            'start_cta_label_ar' => 'nullable|string|max:255',
            'start_cta_url' => 'nullable|string|max:500',
            'footer_tagline_en' => 'nullable|string|max:2000',
            'footer_tagline_ar' => 'nullable|string|max:2000',
            'footer_copyright_en' => 'nullable|string|max:500',
            'footer_copyright_ar' => 'nullable|string|max:500',
            'newsletter_placeholder_en' => 'nullable|string|max:255',
            'newsletter_placeholder_ar' => 'nullable|string|max:255',
            'social_links' => 'nullable|array',
            'contact_email' => 'nullable|email|max:255',
            'contact_email_label_en' => 'nullable|string|max:255',
            'contact_email_label_ar' => 'nullable|string|max:255',
            'contact_email_desc_en' => 'nullable|string|max:500',
            'contact_email_desc_ar' => 'nullable|string|max:500',
            'contact_checklist_en' => 'nullable|array',
            'contact_checklist_ar' => 'nullable|array',
            'contact_pill_en' => 'nullable|string|max:255',
            'contact_pill_ar' => 'nullable|string|max:255',
            'contact_form_label_en' => 'nullable|string|max:255',
            'contact_form_label_ar' => 'nullable|string|max:255',
            'contact_form_title_en' => 'nullable|string|max:500',
            'contact_form_title_ar' => 'nullable|string|max:500',
            'contact_form_lead_en' => 'nullable|string|max:2000',
            'contact_form_lead_ar' => 'nullable|string|max:2000',
            'contact_side_label_en' => 'nullable|string|max:255',
            'contact_side_label_ar' => 'nullable|string|max:255',
            'contact_side_title_en' => 'nullable|string|max:500',
            'contact_side_title_ar' => 'nullable|string|max:500',
            'contact_side_lead_en' => 'nullable|string|max:2000',
            'contact_side_lead_ar' => 'nullable|string|max:2000',
            'contact_response_note_en' => 'nullable|string|max:500',
            'contact_response_note_ar' => 'nullable|string|max:500',
            'blogs_pill_en' => 'nullable|string|max:255',
            'blogs_pill_ar' => 'nullable|string|max:255',
            'blogs_title_en' => 'nullable|string|max:500',
            'blogs_title_ar' => 'nullable|string|max:500',
            'blogs_subtitle_en' => 'nullable|string|max:2000',
            'blogs_subtitle_ar' => 'nullable|string|max:2000',
        ]);

        $settings->update($validated);

        return $this->statusOk([
            'settings' => new JsonDataResource($settings->fresh()),
        ]);
    }
}
