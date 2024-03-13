<?php

namespace Statamic\Translator;

class TextDirection
{
    const RTL = [
        'ar',
        'fa',
        'he',
        'ps',
        'ur',
    ];

    public static function getDirection($shortLocale)
    {
        return in_array($shortLocale, static::RTL) ? 'rtl' : 'ltr';
    }
}
