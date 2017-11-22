<?php

namespace Statamic\Addons\LocaleSettings;

use Statamic\Extend\Fieldtype;

class LocaleSettingsFieldtype extends Fieldtype
{
    public function preProcess($data)
    {
        $processed = [];

        foreach ($data as $locale => $config) {
            $config['locale'] = $locale;
            $processed[] = $config;
        }

        return $processed;
    }

    public function process($data)
    {
        $processed = [];

        foreach ($data as $config) {
            $locale = $config['locale'];
            unset($config['locale']);
            $processed[$locale] = $config;
        }

        return $processed;
    }
}
