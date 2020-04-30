<?php

namespace Statamic\Translator;

class Util
{
    public static function isKey($string)
    {
        // It's considered a translation key if:
        // - it has a dot (eg. "foo.bar")
        // - the dot is *not* at the end of the string (eg. "Hello.")
        // - there's not a space after the dot (eg. "No. Forking. Way.")
        // - there's not another dot after the dot. (eg. "What...")
        return preg_match('/\.(?![\.\s]).+/', $string);
    }

    public static function isString($string)
    {
        return ! static::isKey($string);
    }
}
