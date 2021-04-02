<?php

namespace Statamic\Support;

class DateFormatter
{
    // Credit goes to:
    // https://stackoverflow.com/questions/30186611/php-dateformat-to-moment-js-format

    public static function fromPhpToMoment($date)
    {
        $replacements = [
            'A' => 'A',      // for the sake of escaping below
            'a' => 'a',      // for the sake of escaping below
            'B' => '',       // Swatch internet time (.beats), no equivalent
            'c' => 'YYYY-MM-DD[T]HH:mm:ssZ', // ISO 8601
            'D' => 'ddd',
            'd' => 'DD',
            'e' => 'zz',     // deprecated since version 1.6.0 of moment.js
            'F' => 'MMMM',
            'G' => 'H',
            'g' => 'h',
            'H' => 'HH',
            'h' => 'hh',
            'I' => '',       // Daylight Saving Time? => moment().isDST();
            'i' => 'mm',
            'j' => 'D',
            'L' => '',       // Leap year? => moment().isLeapYear();
            'l' => 'dddd',
            'M' => 'MMM',
            'm' => 'MM',
            'N' => 'E',
            'n' => 'M',
            'O' => 'ZZ',
            'o' => 'YYYY',
            'P' => 'Z',
            'r' => 'ddd, DD MMM YYYY HH:mm:ss ZZ', // RFC 2822
            'S' => 'o',
            's' => 'ss',
            'T' => 'z',      // deprecated since version 1.6.0 of moment.js
            't' => '',       // days in the month => moment().daysInMonth();
            'U' => 'X',
            'u' => 'SSSSSS', // microseconds
            'v' => 'SSS',    // milliseconds (from PHP 7.0.0)
            'W' => 'W',      // for the sake of escaping below
            'w' => 'e',
            'Y' => 'YYYY',
            'y' => 'YY',
            'Z' => '',       // time zone offset in minutes => moment().zone();
            'z' => 'DDD',
        ];

        // Converts escaped characters.
        foreach ($replacements as $from => $to) {
            $replacements['\\'.$from] = '['.$from.']';
        }

        return strtr($date, $replacements);
    }

    // Credit goes to:
    // https://stackoverflow.com/questions/30186611/php-dateformat-to-moment-js-format

    public static function fromMomentToPhp($format)
    {
        $replacements = [
            'DD'   => 'd',
            'ddd'  => 'D',
            'D'    => 'j',
            'dddd' => 'l',
            'E'    => 'N',
            'o'    => 'S',
            'e'    => 'w',
            'DDD'  => 'z',
            'W'    => 'W',
            'MMMM' => 'F',
            'MM'   => 'm',
            'MMM'  => 'M',
            'M'    => 'n',
            'YYYY' => 'Y',
            'YY'   => 'y',
            'a'    => 'a',
            'A'    => 'A',
            'h'    => 'g',
            'H'    => 'G',
            'hh'   => 'h',
            'HH'   => 'H',
            'mm'   => 'i',
            'ss'   => 's',
            'SSS'  => 'u',
            'zz'   => 'e',
            'X'    => 'U',
        ];

        $phpFormat = strtr($format, $replacements);

        return $phpFormat;
    }
}
