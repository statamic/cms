<?php

namespace Statamic\Preferences;

use Statamic\Facades\Preference;

class CorePreferences
{
    public function boot()
    {
        Preference::register('locale', [
            'type' => 'select',
            'instructions' => 'The preferred language for the control panel.',
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
            'instructions' => 'The page to be should be shown when logging into the control panel.',
        ]);

        Preference::register('favorites', [
            'type' => 'grid',
            'instructions' => 'Shortcuts that will be shown when opening the global search bar. You may alternatively visit the page and use the pin icon at the top to add it to this list.',
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
                        'instructions' => 'Should be relative to the control panel.',
                    ],
                ],
            ],
        ]);
    }
}
