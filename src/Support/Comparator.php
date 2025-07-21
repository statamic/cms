<?php

namespace Statamic\Support;

use Collator;
use Statamic\Facades\Site;

class Comparator
{
    protected $locale;
    private static bool $canUseCollator;

    public function __construct()
    {
        $this->locale = Site::current()->locale();

        if (! isset(self::$canUseCollator)) {
            self::$canUseCollator = class_exists(Collator::class);
        }
    }

    public function locale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Compares two values.
     *
     * Returns 1 if first is greater, -1 if second is, 0 if same
     */
    public function values($one, $two): int
    {
        // something is null
        if (is_null($one) || is_null($two)) {
            // place nulls after non-nulls
            if (! is_null($one) && is_null($two)) {
                return 1;
            } elseif (is_null($one) && ! is_null($two)) {
                return -1;
            }

            return 0;
        }

        // something is an array
        if (is_array($one) || is_array($two)) {
            if (is_array($one) && ! is_array($two)) {
                return 1;
            } elseif (! is_array($one) && is_array($two)) {
                return -1;
            }

            return 0;
        }

        // something is an object
        if (is_object($one) || is_object($two)) {
            if (is_object($one) && ! is_object($two)) {
                return 1;
            } elseif (! is_object($one) && is_object($two)) {
                return -1;
            }

            return 0;
        }

        // something is a boolean
        if (is_bool($one) || is_bool($two)) {
            if ($one && ! $two) {
                return 1;
            } elseif (! $one && $two) {
                return -1;
            }

            return 0;
        }

        // string based
        if (! is_numeric($one) || ! is_numeric($two)) {
            return $this->strings($one, $two);
        }

        return $this->numbers($one, $two);
    }

    /**
     * Compares two strings.
     *
     * Returns 1 if first is greater, -1 if second is, 0 if same
     */
    public function strings(string $one, string $two): int
    {
        $one = mb_strtolower($one);
        $two = mb_strtolower($two);

        if (! self::$canUseCollator) {
            return strcmp($one, $two);
        }

        return (new Collator($this->locale))->compare($one, $two);
    }

    /**
     * Compares two numbers.
     *
     * Returns 1 if first is greater, -1 if second is, 0 if same
     */
    public function numbers($one, $two): int
    {
        if ($one > $two) {
            return 1;
        } elseif ($one < $two) {
            return -1;
        }

        return 0;
    }

    public function isQueryBuilder($value)
    {
        return $value instanceof \Statamic\Contracts\Query\Builder
            || $value instanceof \Illuminate\Database\Query\Builder
            || $value instanceof \Illuminate\Database\Eloquent\Builder;
    }
}
