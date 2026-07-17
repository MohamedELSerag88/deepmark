<?php

namespace App\Services\Marketing;

use App\Http\Resources\Mobile\Marketing\BrandNameSuggestionResource;
use App\Http\Resources\Mobile\Marketing\FaqResource;
use App\Http\Resources\Mobile\Marketing\HomeSectionResource;
use App\Http\Resources\Mobile\Marketing\PricingPackageResource;
use App\Http\Resources\Mobile\Marketing\SiteSettingResource;
use App\Models\BrandNameSuggestion;
use App\Models\Faq;
use App\Models\HomeSection;
use App\Models\PricingPackage;
use App\Models\SiteSetting;

class MarketingHomeService
{
    public function build(): array
    {
        $settings = SiteSetting::current();
        $sections = HomeSection::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $sectionsMap = [];
        foreach ($sections as $section) {
            $sectionsMap[$section->section_key] = (new HomeSectionResource($section))->resolve();
        }

        $projects = BrandNameSuggestion::query()
            ->forMarketing()
            ->latest()
            ->limit(12)
            ->get();

        $faqs = Faq::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $pricing = PricingPackage::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return [
            'settings' => (new SiteSettingResource($settings))->resolve(),
            'sections' => $sectionsMap,
            'projects' => BrandNameSuggestionResource::collection($projects)->resolve(),
            'faqs' => FaqResource::collection($faqs)->resolve(),
            'pricing' => PricingPackageResource::collection($pricing)->resolve(),
        ];
    }
}
