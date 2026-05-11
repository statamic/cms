<?php

namespace Statamic\Support;

class DateFormat
{
    public static function containsTime($format)
    {
        return Str::contains($format, ['a', 'A', 'B', 'g', 'G', 'h', 'H', 'i', 's', 'u', 'v', 'U', 'c', 'r']);
    }
}
