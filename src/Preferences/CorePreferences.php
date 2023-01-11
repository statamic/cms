<?php

namespace Statamic\Preferences;

use Statamic\Facades\Preference;

class CorePreferences
{
    public function boot()
    {
        Preference::register('locale', [
            'type' => 'select',
            'display' => __('Locale'),
            'instructions' => __('statamic::messages.preference_locale_instructions'),
            'clearable' => true,
            'options' => [
                'da' => 'Danish',
                'de' => 'German',
                'de_CH' => 'German (Switzerland)',
                'en' => 'English',
                'es' => 'Spanish',
                'fr' => 'French',
                'hu' => 'Hungarian',
                'id' => 'Indonesian',
                'it' => 'Italian',
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
                'zh_CN' => 'Chinese (China)',
                'zh_TW' => 'Chinese (Taiwan)',
            ],
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
                        'display' => 'URL',
                        'type' => 'text',
                    ],
                ],
            ],
        ]);
    }
}
