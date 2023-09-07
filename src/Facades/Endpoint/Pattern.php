<?php

namespace Statamic\Facades\Endpoint;

/**
 * Regular expressions, et al.
 */
class Pattern
{
    /**
     * Order key for date.
     *
     * 4 digits, dash, 2 digits, dash, 2 digits, period.
     */
    public function date()
    {
        return "/(\d{4})\-(\d{2})\-(\d{2})\./";
    }

    /**
     * Order key for datetime.
     *
     * 4 digits, dash, 2 digits, dash, 2 digits, dash, 4 digits, period.
     */
    public function dateTime()
    {
        return "/(\d{4})\-(\d{2})\-(\d{2})\-(\d{4})\./";
    }

    /**
     * Order key for date _or_ datetime.
     *
     * 4 digits, dash, 2 digits, dash, 2 digits
     * Then optionally a dash and 4 digits
     * Period.
     */
    public function dateOrDateTime()
    {
        return "/(\d{4})\-(\d{2})\-(\d{2})(?:\-(\d{4}))?\./";
    }

    /**
     * Order key for numerics.
     *
     * Any number of digits, period.
     */
    public function numeric()
    {
        return "/(\d+)\./";
    }

    /**
     * Any order key.
     *
     * Either:
     *   A date or datetime: 4dig, -, 2dig, -, 2dig, (then opt. -, 2dig)
     *   or any number of digits
     * Followed by a period
     */
    public function orderKey()
    {
        return "/^((?:(\d{4})\-(\d{2})\-(\d{2})(?:\-(\d{4}))?)|(\d+))\./";
    }

    /**
     * A UUID.
     */
    public function uuid()
    {
        return "/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i";
    }

    /**
     * Checks to see if a given $haystack starts with a given $needle.
     *
     * @param  string  $haystack  String to check within
     * @param  string  $needle  String to look for
     * @return bool
     */
    public function startsWith($haystack, $needle)
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }

    /**
     * Checks to see if a given $haystack ends with a given $needle.
     *
     * @param  string  $haystack  String to check within
     * @param  string  $needle  String to look for
     * @return bool
     */
    public function endsWith($haystack, $needle)
    {
        return strcasecmp(substr($haystack, strlen($haystack) - strlen($needle)), $needle) === 0;
    }

    /**
     * Checks to see if a given $value is a valid UUID.
     *
     * @param  string  $value
     * @return bool
     */
    public function isUUID($value)
    {
        return (bool) preg_match($this->uuid(), $value);
    }
}
