<?php namespace Statamic\API;

/**
 * Regular expressions, et al.
 */
class Pattern
{
    /**
     * Order key for date
     *
     * 4 digits, dash, 2 digits, dash, 2 digits, period.
     */
    const DATE = "/(\d{4})\-(\d{2})\-(\d{2})\./";

    /**
     * Order key for datetime
     *
     * 4 digits, dash, 2 digits, dash, 2 digits, dash, 4 digits, period.
     */
    const DATETIME = "/(\d{4})\-(\d{2})\-(\d{2})\-(\d{4})\./";

    /**
     * Order key for date _or_ datetime
     *
     * 4 digits, dash, 2 digits, dash, 2 digits
     * Then optionally a dash and 4 digits
     * Period.
     */
    const DATE_OR_DATETIME = "/(\d{4})\-(\d{2})\-(\d{2})(?:\-(\d{4}))?\./";

    /**
     * Order key for numerics
     *
     * Any number of digits, period.
     */
    const NUMERIC = "/(\d+)\./";

    /**
     * Any order key
     *
     * Either:
     *   A date or datetime: 4dig, -, 2dig, -, 2dig, (then opt. -, 2dig)
     *   or any number of digits
     * Followed by a period
     */
    const ORDER_KEY = "/^((?:(\d{4})\-(\d{2})\-(\d{2})(?:\-(\d{4}))?)|(\d+))\./";

    /**
     * A UUID
     */
    const UUID = "/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i";

    /**
     * Checks to see if a given $haystack starts with a given $needle
     *
     * @param string $haystack  String to check within
     * @param string $needle    String to look for
     * @return bool
     */
    public static function startsWith($haystack, $needle)
    {
        return (substr($haystack, 0, strlen($needle)) === $needle);
    }

    /**
     * Checks to see if a given $haystack ends with a given $needle
     *
     * @param string $haystack  String to check within
     * @param string $needle    String to look for
     * @return bool
     */
    public static function endsWith($haystack, $needle)
    {
        return (strcasecmp(substr($haystack, strlen($haystack) - strlen($needle)), $needle) === 0);
    }

    /**
     * Checks to see if a given $value is a valid UUID
     *
     * @param string $value
     * @return bool
     */
    public static function isUUID($value)
    {
        return (bool) preg_match(self::UUID, $value);
    }
}
