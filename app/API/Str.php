<?php

namespace Statamic\API;

use Collator;
use Stringy\StaticStringy;

/**
 * Manipulating strings
 */
class Str extends \Illuminate\Support\Str
{
    public static function __callStatic($method, $parameters)
    {
        return call_user_func_array([StaticStringy::class, $method], $parameters);
    }

    public static function studlyToSlug($string)
    {
        return Str::slug(Str::snake($string));
    }

    public static function isUrl($string)
    {
        return self::startsWith($string, ['http://', 'https://', '/']);
    }

    public static function deslugify($string)
    {
        return str_replace(['-', '_'], ' ', $string);
    }

    /**
     * Get the human file size of a given file.
     *
     * @param int $bytes
     * @param int $decimals
     * @return string
     */
    public static function fileSizeForHumans($bytes, $decimals = 2)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, $decimals) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, $decimals) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, $decimals) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' B';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' B';
        } else {
            $bytes = '0 B';
        }

        return $bytes;
    }

    /**
     * Locale based string comparison.
     *
     * @param  string  $str1  First string to compare.
     * @param  string  $str2  Second string to compare.
     * @param  string|null  $locale  A locale key.
     * @param  bool $caseSensitive  Whether the comparison should be case sensitive.
     * @return int|false  Return comparison result, or FALSE on error.
     */
    public static function compare($str1, $str2, $locale = null, $caseSensitive = true)
    {
        if (! $caseSensitive) {
            $str1 = self::lower($str1);
            $str2 = self::lower($str2);
        }

        if (! class_exists('Collator')) {
            return strcmp($str1, $str2);
        }

        // A locale key should be provided. We'll look up the corresponding full locale code to be
        // used within the collator. If none is provided it will fall back to the default locale.
        $locale = Config::getFullLocale($locale);

        return (new Collator($locale))->compare($str1, $str2);
    }
}
