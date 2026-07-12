<?php

namespace Statamic\Support;

class TextDirection
{
    private const RTL = ['ar', 'fa', 'he', 'ps', 'ur'];

    public static function of(string $lang): string
    {
        return in_array($lang, static::RTL) ? 'rtl' : 'ltr';
    }
}
