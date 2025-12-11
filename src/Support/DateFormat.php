<?php

namespace Statamic\Support;

class DateFormat
{
    public static function containsTime($format)
    {
        return Str::contains($format, ['G', 'g', 'H', 'h', 'U', 'c', 'r']);
    }
}
