<?php

namespace Statamic\Preferences;

use Locale;
use Statamic\Facades\Preference;
use Statamic\Statamic;

class CorePreferences
{
    public function boot()
    {
        Preference::register('locale', [
            'type' => 'select',
            'display' => __('Locale'),
            'instructions' => __('statamic::messages.preference_locale_instructions'),
            'clearable' => true,
            'label_html' => true,
            'options' => $this->localeOptions(),
        ]);

        Preference::register('start_page', [
            'type' => 'text',
            'display' => __('Start Page'),
            'instructions' => __('statamic::messages.preference_start_page_instructions'),
        ]);
    }

    private function localeOptions(): array
    {
        $current = Statamic::cpLocale();

        return collect([
            'ar' => 'Arabic',
            'az' => 'Azerbaijani',
            'cs' => 'Czech',
            'da' => 'Danish',
            'de' => 'German',
            'de_CH' => 'German (Switzerland)',
            'en' => 'English',
            'es' => 'Spanish',
            'fa' => 'Persian',
            'fr' => 'French',
            'hu' => 'Hungarian',
            'id' => 'Indonesian',
            'it' => 'Italian',
            'ja' => 'Japanese',
            'ms' => 'Malay',
            'nb' => 'Norwegian',
            'nl' => 'Dutch',
            'pl' => 'Polish',
            'pt' => 'Portuguese',
            'pt_BR' => 'Portuguese (Brazil)',
            'ru' => 'Russian',
            'sl' => 'Slovenian',
            'sv' => 'Swedish',
            'tr' => 'Turkish',
            'uk' => 'Ukrainian',
            'vi' => 'Vietnamese',
            'zh_CN' => 'Chinese (China)',
            'zh_TW' => 'Chinese (Taiwan)',
        ])->when(extension_loaded('intl'), fn ($locales) => $locales
            ->map(fn ($label, $locale) => [
                'label' => Locale::getDisplayName($locale, $current),
                'native' => Locale::getDisplayName($locale, $locale),
            ])
            ->sortBy('native', SORT_NATURAL | SORT_FLAG_CASE)
            ->map(function ($item, $locale) use ($current) {
                ['label' => $label, 'native' => $native] = $item;

                if ($locale !== $current && $label !== $native) {
                    $label .= '<span class="ms-4 text-gray-600">'.$native.'</span>';
                }

                return $label;
            }))->all();
    }
}
