<?php

namespace Statamic\Translator;

use Statamic\Support\Str;

class Util
{
    public static function isKey($string)
    {
        // It's considered a translation key if:
        // - it has a dot (eg. "foo.bar")
        // - the dot is *not* at the end of the string (eg. "Hello.")
        // - there's no spaces (eg. "No. Forking. Way.")
        // - there's not another dot after the dot. (eg. "What...")
        return ! Str::contains($string, ' ') && preg_match('/\.(?![\.]).+/', $string);
    }

    public static function isString($string)
    {
        return ! static::isKey($string);
    }
}
