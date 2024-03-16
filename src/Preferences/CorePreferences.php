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

        Preference::register('favorites', [
            'type' => 'grid',
            'display' => __('Favorites'),
            'instructions' => __('statamic::messages.preference_favorites_instructions'),
            'fields' => [
                [
                    'handle' => 'name',
                    'field' => [
                        'type' => 'text',
                        'width' => 33,
                    ],
                ],
                [
                    'handle' => 'url',
                    'field' => [
                        'display' => __('URL'),
                        'type' => 'text',
                    ],
                ],
            ],
        ]);
    }

    private function localeOptions(): array
    {
        $current = Statamic::cpLocale();

        return collect([
            'cs' => __('locales.czech'),
            'da' => __('locales.danish'),
            'de' => __('locales.german'),
            'de_CH' => __('locales.german_ch'),
            'en' => __('locales.english'),
            'es' => __('locales.spanish'),
            'fa' => __('locales.persian'),
            'fr' => __('locales.french'),
            'hu' => __('locales.hungarian'),
            'id' => __('locales.indonesian'),
            'it' => __('locales.italian'),
            'ja' => __('locales.japanese'),
            'ms' => __('locales.malay'),
            'nb' => __('locales.norwegian'),
            'nl' => __('locales.dutch'),
            'pl' => __('locales.polish'),
            'pt' => __('locales.portuguese'),
            'pt_BR' => __('locales.portuguese_br'),
            'ru' => __('locales.russian'),
            'sl' => __('locales.slovenian'),
            'sv' => __('locales.swedish'),
            'tr' => __('locales.turkish'),
            'zh_CN' => __('locales.chinese_cn'),
            'zh_TW' => __('locales.chinese_tw'),
        ])->when(extension_loaded('intl'), fn ($locales) => $locales
            ->map(fn ($label, $locale) => [
                'label' => Locale::getDisplayName($locale, $current),
                'native' => Locale::getDisplayName($locale, $locale),
            ])
            ->sortBy('native', SORT_NATURAL | SORT_FLAG_CASE)
            ->map(function ($item, $locale) use ($current) {
                ['label' => $label, 'native' => $native] = $item;

                if ($locale !== $current && $label !== $native) {
                    $label .= '<span class="ltr:ml-4 rtl:mr-4 text-gray-600">'.$native.'</span>';
                }

                return $label;
            }))->all();
    }
}
