<?php

namespace App\Http\Resources\Mobile\Marketing;

use App\Http\Helpers\Traits\LocalizesFields;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SiteSettingResource extends JsonResource
{
    use LocalizesFields;

    public function toArray(Request $request): array
    {
        return [
            'brand_name' => $this->pick('brand_name', 'deepmarks'),
            'logo_url' => $this->logo_url,
            'login_cta_label' => $this->pick('login_cta_label', 'Login'),
            'login_cta_url' => $this->login_cta_url,
            'start_cta_label' => $this->pick('start_cta_label', 'Start now'),
            'start_cta_url' => $this->start_cta_url,
            'footer_tagline' => $this->pick('footer_tagline'),
            'footer_copyright' => $this->pick('footer_copyright'),
            'newsletter_placeholder' => $this->pick('newsletter_placeholder'),
            'social_links' => $this->social_links ?? [],
            'contact_email' => $this->contact_email,
            'contact_email_label' => $this->pick('contact_email_label'),
            'contact_email_desc' => $this->pick('contact_email_desc'),
            'contact_checklist' => $this->pick('contact_checklist', []),
            'contact_pill' => $this->pick('contact_pill'),
            'contact_form_label' => $this->pick('contact_form_label'),
            'contact_form_title' => $this->pick('contact_form_title'),
            'contact_form_lead' => $this->pick('contact_form_lead'),
            'contact_side_label' => $this->pick('contact_side_label'),
            'contact_side_title' => $this->pick('contact_side_title'),
            'contact_side_lead' => $this->pick('contact_side_lead'),
            'contact_response_note' => $this->pick('contact_response_note'),
            'blogs_pill' => $this->pick('blogs_pill'),
            'blogs_title' => $this->pick('blogs_title'),
            'blogs_subtitle' => $this->pick('blogs_subtitle'),
        ];
    }
}
