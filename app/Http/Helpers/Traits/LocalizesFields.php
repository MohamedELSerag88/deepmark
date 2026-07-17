<?php

namespace App\Http\Helpers\Traits;

trait LocalizesFields
{
    protected function locale(): string
    {
        $locale = app()->getLocale();
        return in_array($locale, config('app.locales', ['en', 'ar']), true) ? $locale : 'en';
    }

    protected function pick(string $base, $fallback = null)
    {
        $locale = $this->locale();
        $localized = $this->{$base . '_' . $locale} ?? null;
        if ($localized !== null && $localized !== '') {
            return $localized;
        }
        $en = $this->{$base . '_en'} ?? null;
        if ($en !== null && $en !== '') {
            return $en;
        }
        return $fallback;
    }
}
