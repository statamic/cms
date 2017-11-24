<?php

namespace Statamic\View;

use Statamic\API\Arr;
use Statamic\API\Data;
use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\Asset;
use Statamic\API\Parse;
use Statamic\API\Theme;
use Statamic\API\Config;
use Statamic\API\Helper;
use Statamic\API\Str;
use Statamic\API\Content;
use Statamic\Extend\Modifier;
use Statamic\API\Localization;
use Stringy\StaticStringy as Stringy;

class BaseModifiers extends Modifier
{
    /**
     * Adds values together with science. Context aware.
     *
     * @param $value
     * @param $params
     * @return mixed
     */
    public function add($value, $params, $context)
    {
        return $value + $this->getMathModifierNumber($params, $context);
    }

    /**
     * Creates a sentence list from the given array and the ability to set the glue
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function ampersandList($value, $params)
    {
        if (! is_array($value)) return $value;

        $glue         = array_get($params, 0, '&');
        $oxford_comma = array_get($params, 1, false);

        return Helper::makeSentenceList($value, $glue, $oxford_comma);
    }

    /**
     * Scope an array variable
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function scopeAs($value, $params)
    {
        if ( is_array($value)) {
            $as = array_get($params, 0);

            foreach ($value as $key => $data) {
              $value[$key][$as] = $data;
            }

            return $value;
        }
    }

    /**
     * Returns an ASCII version of the string. A set of non-ASCII characters are replaced with their
     * closest ASCII counterparts, and the rest are removed unless instructed otherwise.
     *
     * @param $value
     * @return string
     */
    public function ascii($value)
    {
        return Stringy::toAscii($value);
    }

    /**
     * Returns the character at given index $param[0], starting from 0.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function at($value, $params)
    {
        return Stringy::at($value, array_get($params, 0));
    }

    /**
     * Returns a focal point as a background-position CSS value.
     *
     * @param $value
     * @return string
     */
    public function backgroundPosition($value)
    {
        if (! Str::contains($value, '-')) {
            return $value;
        }

        return vsprintf('%d%% %d%%', explode('-', $value));
    }

    /**
     * Removes a given number ($param[0]) of characters from the end of a variable
     *
     * @param $value
     * @param array $params
     * @return string
     */
    public function backspace($value, $params)
    {
        if (is_array($value) || !isset($params[0]) || !is_numeric($params[0]) || $params[0] < 0)
        {
            return $value;
        }

        return substr($value, 0, -$params[0]);
    }

    /**
     * Returns a camelCase version of the string. Trims surrounding spaces,
     * capitalizes letters following digits, spaces, dashes and underscores,
     * and removes spaces, dashes, as well as underscores.
     *
     * @param $value
     * @return string
     */
    public function camelize($value)
    {
        return Stringy::camelize($value);
    }

    /**
     * Wraps a value in CDATA tags for RSS/XML feeds
     *
     * @param $value
     * @return string
     */
    public function cdata($value)
    {
        return '<![CDATA[' . $value . ']]>';
    }

    /**
     * Rounds a number up to the next whole number
     *
     * @param $value
     * @return int
     */
    public function ceil($value)
    {
        return ceil((float) $value);
    }


    /**
     * Collapses an array of arrays into a flat array
     *
     * @param $value
     * @return array
     */
    public function collapse($value)
    {
        return collect($value)->collapse()->toArray();
    }

    /**
     * Trims the string and replaces consecutive whitespace characters with
     * a single space. This includes tabs and newline characters, as well as
     * multibyte whitespace such as the thin space and ideographic space.
     *
     * @param $value
     * @return string
     */
    public function collapseWhitespace($value)
    {
        return Stringy::collapseWhitespace($value);
    }

    /**
     * Debug a value with a call to JavaScript's console.log
     *
     * @param  $value
     * @return string
     */
    public function consoleLog($value)
    {
        return '<script>
            window.log=function(a){if(this.console){console.log(a);}};
            log('. json_encode($value) .');
        </script>';
    }

    /**
     * Returns true if the string contains $needle, false otherwise. By default,
     * the comparison is case-insensitive, but can be made sensitive by setting $params[1] to true.
     *
     * @param $value
     * @param $params
     * @param $context
     * @return bool
     */
    public function contains($haystack, $params, $context)
    {
        $needle = array_get($context, $params[0], $params[0]);

        if (is_array($haystack)) {
            return in_array($needle, $haystack);
        }

        return Stringy::contains($haystack, $needle, array_get($params, 1, false));
    }

    /**
     * Returns true if the string contains all needles ($params), false otherwise. Will check context before
     * assuming it's a locally defined set. Case-insensitive.
     *
     * @param $value
     * @param $params
     * @param $context
     * @return bool
     */
    public function containsAll($value, $params, $context)
    {
        $needles = array_get($context, $params[0], $params);

        return Stringy::containsAll($value, $needles);
    }

    /**
     * Returns true if the string contains all needles ($params), false otherwise. Case-insensitive.
     *
     * @param $value
     * @param $params
     * @return bool
     */
    public function containsAny($value, $params)
    {
        return Stringy::containsAny($value, $params);
    }

    /**
     * Returns the number of items in an array
     * @param  $value
     * @param  $params
     * @return int
     */
    public function count($value, $params)
    {
        return count($value);
    }

    /**
     * Returns the number of occurrences of $params[0] in the given string. By default,
     * the comparison is case-insensitive, but can be made sensitive by setting $params[1] to true.
     *
     * @param $value
     * @param $params
     * @return int
     */
    public function countSubstring($value, $params)
    {
        return Stringy::countSubstr($value, array_get($params, 0), array_get($params, 1, false));
    }

    /**
     * Returns a lowercase and trimmed string separated by dashes. Dashes are inserted before uppercase
     * characters (with the exception if the first character of the string), and in
     * place of spaces as well as underscores.
     *
     * @param $value
     * @return string
     */
    public function dashify($value)
    {
        return Stringy::dasherize($value);
    }

    /**
     * Get the date difference in days
     *
     * @param Carbon  $value
     * @param $params
     *
     * @return integer
     */
    public function daysAgo($value, $params)
    {
        return carbon($value)->diffInDays(array_get($params, 0));
    }

    /**
     * Dump a var into the Debug bar for data exploration
     *
     * @param $value
     */
    public function debug($value)
    {
        debug($value);
    }

    /**
     * Convert entities to HTML characters.
     *
     * @param  string  $value
     * @return string
     */
    public function decode($value)
    {
        return app('html')->decode($value);
    }

    /**
     * Replaces hyphens and underscores with spaces
     *
     * @param $value
     * @return string
     */
    public function deslugify($value)
    {
        return trim(preg_replace('~[-_]~', ' ', $value), " ");
    }

    /**
     * Divides values with some science. Context aware.
     *
     * @param $value
     * @param $params
     * @return mixed
     */
    public function divide($value, $params, $context)
    {
        return $value / $this->getMathModifierNumber($params, $context);
    }

    /**
     * Turn an array into an definition list
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function dl($value, $params)
    {
        return app('html')->dl($value, $this->buildAttributesFromParameters($params));
    }

    /**
     * Dump and die the output of a variable
     *
     * @param $value
     */
    public function dump($value)
    {
        dd($value);
    }

    /**
     * Returns true if the string ends with a given substring, false otherwise.
     * The comparison is case-insensitive.
     *
     * @param $value
     * @param $params
     * @return bool
     */
    public function endsWith($value, $params)
    {
        return Stringy::endsWith($value, array_get($params, 0), false);
    }

    /**
     * Ensures that the string begins with a specified string.
     * If it doesn't, it's prepended.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function ensureLeft($value, $params)
    {
        return Stringy::ensureLeft($value, array_get($params, 0));
    }

    /**
     * Ensures that the string ends with a specified string. If it doesn't, it's appended.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function ensureRight($value, $params)
    {
        return Stringy::ensureRight($value, array_get($params, 0));
    }

    /**
     * Convert an HTML string to entities.
     *
     * @param  string  $value
     * @return string
     */
    public function entities($value)
    {
        return app('html')->entities($value);
    }

    /**
     * Breaks a string at a given marker.
     * Uses <!--more--> by default
     *
     * @param $value
     * @param array $params
     * @return string
     */
    public function excerpt($value, $params)
    {
        if (is_array($value)) return $value;

        $breaker = array_get($params, 0, '<!--more-->');

        return strstr($value, $breaker, true);
    }

    /**
     * Just like the PHP method, breaks a string into an array on a specified key, $params[0]
     *
     * @param $value
     * @param $params
     * @return array
     */
    public function explode($value, $params)
    {
        return explode(array_get($params, 0), $value);
    }

    /**
     * Returns the file extension of a given filename.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function extension($value, $params)
    {
        return pathinfo($value, PATHINFO_EXTENSION);
    }

    /**
     * Generate a link to a Favicon file.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function favicon($value, $params)
    {
        return app('html')->favicon($value, $this->buildAttributesFromParameters($params));
    }

    /**
     * Returns the first $params[0] characters of a string, or the last element of an array.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function first($value, $params)
    {
        if (is_array($value)) {
            return array_get($value, 0);
        }

        return Stringy::first($value, array_get($params, 0));
    }

    /**
     * Flattens a multi-dimensional collection into a single dimension.
     *
     * @param $value
     * @return array
     */
    public function flatten($value)
    {
        return collect($value)->flatten()->toArray();
    }

    /**
     * Swaps the keys with their corresponding values
     *
     * @param $value
     * @return array
     */
    public function flip($value)
    {
        return array_flip($value);
    }

    /**
     * Rounds a number down to the next whole number
     *
     * @param $value
     * @return int
     */
    public function floor($value)
    {
        return floor((float) $value);
    }

    /**
     * Converts a string to a Carbon instance and formats it according to the whim of the Overlord
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function format($value, $params)
    {
        return carbon($value)->format(array_get($params, 0));
    }

    /**
     * Converts a string to a Carbon instance and formats it according to the whim of the Overlord
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function formatLocalized($value, $params)
    {
        return carbon($value)->formatLocalized(array_get($params, 0));
    }

    /**
     * Format a number with grouped thousands and decimal points
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function formatNumber($value, $params)
    {
        $precision = array_get($params, 0, 0);
        $dec_point = array_get($params, 1, '.');
        $thousands_sep = array_get($params, 2, ',');

        $number = floatval(str_replace(',', '', $value));

        return number_format($number, $precision, $dec_point, $thousands_sep);
    }

    /**
     * Replace /absolute/urls with http://domain.com/urls
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function fullUrls($value, $params)
    {
        $domain = Config::getSiteURL();

        return preg_replace_callback('/="(\/[^"]+)"/ism', function($item) use ($domain) {
            return '="' . Path::tidy($domain . $item[1]) . '"';
        }, $value);
    }

    /**
     * Get any variable from a relationship
     *
     * @param $value
     * @return string
     */
    public function get($value, $params)
    {
        // If the requested value is an array, we'll just grab the first one.
        if (is_array($value)) {
            $value = array_get($value, 0);
        }

        // If the requested value (it should be an ID) doesn't exist, we'll just
        // spit the value back as-is. This seems like a sensible solution here.
        if (! $item = Data::find($value)) {
            return $value;
        }

        // Get the requested variable, which is the first parameter.
        $var = array_get($params, 0);

        // Convert the item to an array, since we'll want access to all the
        // supplemented data. Then grab the requested variable from there.
        if ($arrayValue = array_get($item->toArray(), $var)) {
            return $arrayValue;
        }

        // Finally, try to call a method on the object
        $method = Str::slug($var);
        if (method_exists($item, $method)) {
            return $item->$method();
        }

        // If after all is said and done, there's still nothing, just show the original value.
        return $value;
    }

    /**
     * Get a Gravatar image URL from an email
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function gravatar($value, $params)
    {
        return gravatar($value, array_get($params, 0));
    }

    /**
     * Groups the collection's items by a given key
     *
     * @param $value
     * @param $params
     * @return array
     */
    public function groupBy($value, $params)
    {
        return collect($value)->groupBy($params[0])->toArray();
    }

    /**
     * Returns true if the string contains a lowercase character, false otherwise.
     *
     * @param $value
     * @return bool
     */
    public function hasLowerCase($value)
    {
        return Stringy::hasLowerCase($value);
    }

    /**
     * Returns true if the string contains an uppercase character, false otherwise.
     *
     * @param $value
     * @return bool
     */
    public function hasUpperCase($value)
    {
        return Stringy::hasUpperCase($value);
    }

    /**
     * Get the date difference in hours
     *
     * @param Carbon  $value
     * @param $params
     *
     * @return integer
     */
    public function hoursAgo($value, $params)
    {
        return carbon($value)->diffInHours(array_get($params, 0));
    }

    /**
     * Generate an HTML image element.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function image($value, $params)
    {
        return '<img src="'.$value.app('html')->attributes($this->buildAttributesFromParameters($params)).'">';
    }

    /**
     * Turn an array into a string and glue together with a delimiter.
     * Joinplode because join and implode are existing PHP methods. Obviously.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function joinplode($value, $params)
    {
        // Workaround to support pipe characters. If there are multiple params
        // that means a pipe was used. We'll just join them for now.
        if (count($params) > 1) {
            $params = [join('|', $params)];
        }

        return implode(array_get($params, 0, ', '), $value);
    }

    /**
     * Check if an item exists in an array using "dot" notation.
     *
     * @param $value
     * @param $params
     * @return bool
     */
    public function inArray($value, $params, $context)
    {
        $array = array_get($context, $params[0], $params);

        return in_array($value, $array);
    }

    /**
     * Inserts $substring into the string at the $position provided.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function insert($value, $params)
    {
        $substring = array_get($params, 0);
        $position = array_get($params, 1);

        return Stringy::insert($value, $substring, $position);
    }

    /**
     * Determines if the date is after another specified date ($params[0])
     *
     * @param $value
     * @param $params
     * @param $context
     * @return bool
     */
    public function isAfter($value, $params, $context)
    {
        $date = carbon(array_get($context, $params[0], $params[0]));

        return carbon($value)->gt($date);
    }

    /**
     * Returns true if the string contains only alphabetic chars, false otherwise.
     *
     * @param $value
     * @return bool
     */
    public function isAlpha($value)
    {
        return Stringy::isAlpha($value);
    }

    /**
     * Returns true if the string contains only alphabetic and numeric chars, false otherwise.
     *
     * @param $value
     * @return bool
     */
    public function isAlphanumeric($value)
    {
        return Stringy::isAlphanumeric($value);
    }

    /**
     * Determines if the date is before another specified date ($params[0])
     *
     * @param $value
     * @param $params
     * @param $context
     * @return bool
     */
    public function isBefore($value, $params, $context)
    {
        $date = carbon(array_get($context, $params[0], $params[0]));

        return carbon($value)->lt($date);
    }

    /**
     * Determines if the date is between two other specified dates, $params[0] and $params[1]
     *
     * @param $value
     * @param $params
     * @param $context
     * @return bool
     */
    public function isBetween($value, $params, $context)
    {
        $date1 = carbon(array_get($context, $params[0], $params[0]));
        $date2 = carbon(array_get($context, $params[1], $params[1]));

        return carbon($value)->between($date1, $date2);
    }

    /**
     * Returns true if the string contains only whitespace chars, false otherwise.
     *
     * @param $value
     * @return bool
     */
    public function isBlank($value)
    {
        return Stringy::isBlank($value);
    }

    /**
     * Checks to see if an array is empty. Like, for realsies.
     *
     * @param $value
     * @return bool
     */
    public function isEmpty($value)
    {
        return Helper::isEmptyArray($value);
    }

    /**
     * Determines if the date is in the future, ie. greater (after) than now
     *
     * @param $value
     * @return bool
     */
    public function isFuture($value)
    {
        return carbon($value)->isFuture();
    }

    /**
     * Returns true if the string is JSON, false otherwise.
     *
     * @param $value
     * @return bool
     */
    public function isJson($value)
    {
        return Stringy::isJson($value);
    }

    /**
     * Determines if the date in a leap year
     *
     * @param $value
     * @return bool
     */
    public function isLeapYear($value)
    {
        return carbon($value)->isLeapYear();
    }

    /**
     * Returns true if the string contains only lowercase chars, false otherwise.
     *
     * @param $value
     * @return bool
     */
    public function isLowercase($value)
    {
        return Stringy::isLowercase($value);
    }

    /**
     * Finds whether a value is a number or a numeric string
     *
     * @param $value
     * @return bool
     */
    public function isNumeric($value)
    {
        return is_numeric($value);
    }

    /**
     * Determines if the date is in the past, ie. less (before) than now
     *
     * @param $value
     * @return bool
     */
    public function isPast($value)
    {
        return carbon($value)->isPast();
    }

    /**
     * Determines if the date is today
     *
     * @param $value
     * @return bool
     */
    public function isToday($value)
    {
        return carbon($value)->isToday();
    }

    /**
     * Returns true if the string contains only uppercase chars, false otherwise.
     *
     * @param $value
     * @return bool
     */
    public function isUppercase($value)
    {
        return Stringy::isUpperCase($value);
    }

    /**
     * Determines if the date on a weekday
     *
     * @param $value
     * @return bool
     */
    public function isWeekday($value)
    {

        return carbon($value)->isWeekday();
    }

    /**
     * Determines if the date on a weekend
     *
     * @param $value
     * @return bool
     */
    public function isWeekend($value)
    {
        return carbon($value)->isWeekend();
    }

    /**
     * Determines if the date is yesterday
     *
     * @param $value
     * @return bool
     */
    public function isYesterday($value)
    {
        return carbon($value)->isYesterday();
    }

    /**
     * Returns the last $params[0] characters of a string, or the last element of an array.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function last($value, $params)
    {
        if (is_array($value)) {
            return array_pop($value);
        }

        return Stringy::last($value, array_get($params, 0));
    }

    /**
     * Converts the first character of the supplied string to lower case.
     *
     * @param $value
     * @return string
     */
    public function lcfirst($value)
    {
        return Stringy::lowerCaseFirst($value);
    }

    /**
     * Get the items in an array or characters in a string
     *
     * @param $value
     * @return int
     */
    public function length($value)
    {
        return (is_array($value)) ? count($value) : Stringy::length($value);
    }

    /**
     * Limit the number of items in an array
     *
     * @param $value
     * @param $params
     * @return array
     */
    public function limit($value, $params)
    {
        return array_slice($value, 0, array_get($params, 0, 0));
    }

    /**
     * Generate an HTML link.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function link($value, $params)
    {
        $attributes = $this->buildAttributesFromParameters($params);
        $title = array_pull($attributes, 'title', null);

        return app('html')->link($value, $title, $attributes);
    }

    /**
     * Converts all characters in the string to lowercase. Multi-byte friendly.
     *
     *
     * @param $value
     * @return string
     */
    public function lower($value)
    {
        return Stringy::toLowerCase($value);
    }

    /**
     * Replace a var with a localized string
     *
     * @param $value
     * @return string
     */
    public function localize($value)
    {
        return Localization::fetch($value);
    }

    /**
     * Rough macro prototype that only uses BaseModifiers
     *
     * @param $value
     * @param $params
     * @param $context
     * @return mixed
     */
    public function macro($value, $params, $context)
    {
        $macro = array_get($params, 0);

        return collect(Theme::getMacro($macro))->map(function ($params, $name) {
            return compact('name', 'params');
        })->reduce(function ($value, $modifier) use ($context) {
            return Modify::value($value)->context($context)->modify($modifier['name'], $modifier['params']);
        }, $value);
    }

    /**
     * Generate a HTML link to an email address.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function mailto($value, $params)
    {
        return app('html')->mailto($value, null, $this->buildAttributesFromParameters($params));
    }

    /**
     * Parse content as Markdown
     *
     * @param $value
     * @return mixed
     */
    public function markdown($value)
    {
        return markdown($value);
    }

    /**
     * Merge an array variable with another array variable
     *
     * @param $value
     * @param $params
     * @param $context
     * @return array
     */
    public function merge($value, $params, $context)
    {
        $to_merge = (array) array_get($context, $params[0], $context);

        return array_merge($value, $to_merge);
    }

    /**
     * Get the date difference in minutes
     *
     * @param Carbon  $value
     * @param $params
     *
     * @return integer
     */
    public function minutesAgo($value, $params)
    {
        return carbon($value)->diffInMinutes(array_get($params, 0));
    }

    /**
     * Performs modulus division on a value. Context aware.
     *
     * @param $value
     * @param $params
     * @return int
     */
    public function mod($value, $params, $context)
    {
        $number = array_get($context, $params[0], $params[0]);

        return ($value % $number);
    }

    /**
     * Alters the timestamp by incrementing or decremting in a format acceted by strtotime()
     *
     * @link http://php.net/manual/en/function.strtotime.php
     *
     * @param $value
     * @param $params
     * @return \DateTime
     */
    public function modifyDate($value, $params)
    {
        return carbon($value)->modify(array_get($params, 0));
    }

    /**
     * Get the date difference in months
     *
     * @param Carbon  $value
     * @param $params
     *
     * @return integer
     */
    public function monthsAgo($value, $params)
    {
        return carbon($value)->diffInMonths(array_get($params, 0));
    }

    /**
     * Multiplies values together with a little help from science. Context aware.
     *
     * @param $value
     * @param $params
     * @return mixed
     */
    public function multiply($value, $params, $context)
    {
        return $value * $this->getMathModifierNumber($params, $context);
    }

    /**
     * It's kinda neat!
     *
     * @param $value
     * @return string
     */
    public function neatify($value)
    {
        return $value . ' is pretty neat!';
    }

    /**
     * Replaces line breaks with <br> tags
     *
     * @param $value
     * @return string
     */
    public function nl2br($value)
    {
        return nl2br($value);
    }

    /**
     * Is it or is it not numberwang?
     *
     * @param $value
     * @return bool
     */
    public function isNumberwang($value)
    {
        return in_array($value, [1, 22, 7, 9, 1002, 2.3, 15, 109876567, 31]);
    }

    /**
     * Obfuscate a string to prevent spam-bots from sniffing it.
     *
     * @param $value
     * @return string
     */
    public function obfuscate($value)
    {
        return app('html')->obfuscate($value);
    }

    /**
     * Obfuscate an e-mail address to prevent spam-bots from sniffing it.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function obfuscateEmail($value, $params)
    {
        return app('html')->email($value, null, $this->buildAttributesFromParameters($params));
    }

    /**
     * Turn an array into an ordered list
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function ol($value, $params)
    {
        return app('html')->ol($value, $this->buildAttributesFromParameters($params));
    }

    /**
     * Turn an array into a pipe delimited list
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function optionList($value, $params)
    {
        if (count($params) > 1) {
            $params = [join('|', $params)];
        }

        return implode(array_get($params, 0, '|'), $value);
    }

    /**
     * Offset the items in an array
     *
     * @param $value
     * @param $params
     * @return array
     */
    public function offset($value, $params)
    {
        return array_slice($value, array_get($params, 0, 0));
    }

    /**
     * Get the output of an Asset, useful for SVGs
     *
     * @param $value
     * @return array
     */
    public function output($value)
    {
        $asset = Asset::find($value);

        if ($asset) {
            return $asset->disk()->get($asset->path());
        }
    }

    /**
     * Renders an array variable with a partial, context aware
     * @param  $value
     * @param  $params
     * @return [string
     */
    public function partial($value, $params, $context)
    {
        $name = array_get($context, $params[0], $params[0]);

        $partial = 'partials/' . $name . '.html';

        return Parse::template(File::disk('theme')->get($partial), $value);
    }

    /**
     * Get the plural form of an English word with access to $context
     *
     * @param $value
     * @param $params
     * @param $context
     * @return string
     */
    public function plural($value, $params, $context)
    {
        $count = array_get($params, 0);

        if ( ! is_numeric($count)) {
            $count = (int) array_get($context, $count);
        }

        return Str::plural($value, $count);
    }

    /**
     * URL-encode according to RFC 3986
     *
     * @param $value
     * @return string
     */
    public function rawurlencode($value)
    {
        return implode('/', array_map('rawurlencode', explode('/', $value)));
    }

    /**
     * Estimate the read time based on a given number of words per minute
     *
     * @param $value
     * @param $params
     * @return int
     */
    public function readTime($value, $params)
    {
        $words = str_word_count(strip_tags($value));

        return ceil($words / array_get($params, 0, 200));
    }

    /**
     * Replaces all occurrences of pattern $params[0] with the string $params[1].
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function regexReplace($value, $params)
    {
        return Stringy::regexReplace($value, array_get($params, 0), array_get($params, 1));
    }

    /**
     * Format date in an easier for humans to read format.
     * Send $params[1] as true to turn off modifiers "ago", "from now", etc.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function relative($value, $params)
    {
        $remove_modifiers = array_get($params, 0, false);

        return carbon($value)->diffForHumans(null, $remove_modifiers);
    }

    /**
     * Returns a new string with the prefix $params[0] removed, if present.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function removeLeft($value, $params)
    {
        return Stringy::removeLeft($value, array_get($params, 0));
    }

    /**
     * Returns a new string with the suffix $params[0] removed, if present.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function removeRight($value, $params)
    {
        return Stringy::removeRight($value, array_get($params, 0));
    }

    /**
     * Repeats value a given number of times
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function repeat($value, $params)
    {
        return str_repeat($value, (int) array_get($params, 0, 1));
    }

    /**
     * Replaces all occurrences of a search in $params[0] by $params[1].
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function replace($value, $params)
    {
        return Stringy::replace($value, array_get($params, 0), array_get($params, 1));
    }

    /**
     * Reverses the order of a string or list
     *
     * @param $value
     * @return mixed
     */
    public function reverse($value)
    {
        if (is_array($value)) {
            return array_reverse($value);
        }

        return Stringy::reverse($value);
    }

    /**
     * Rounds a number to a specified precision (number of digits after the decimal point)
     * @param $value
     * @param $params
     * @return float
     */
    public function round($value, $params)
    {
        return round($value, (int) array_get($params, 0, 0));
    }

    /**
     * Truncates the string to a given length ($params[0]), while ensuring that
     * it does not split words. If substring ($params[1]) is provided, and truncating occurs,
     * the string is further truncated so that the substring may be
     * appended without exceeding the desired length.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function safeTruncate($value, $params)
    {
        return Stringy::safeTruncate($value, array_get($params, 0, 200), array_get($params, 1, ''));
    }

    /**
     * Convert special characters to HTML entities with htmlspecialchars
     *
     * @param $value
     * @return string
     */
    public function sanitize($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, Config::get('system.charset', 'UTF-8'), false);
    }

    /**
     * Place variables in a scope
     *
     * @param  $value
     * @param  $params
     * @return array
     */
    public function scope($value, $params)
    {
        $scope = array_get($params, 0, 'tag');

        return Arr::addScope($value, $scope);
    }

    /**
     * Returns a segment by number from any valid URL or UI
     *
     * @param  $value
     * @param  $params
     * @param  $context
     * @return string
     */
    public function segment($value, $params, $context)
    {
        // Which segment?
        $segment = array_get($params, 0, 1);

        // Support a variable name
        if (! is_numeric($segment)) {
            $segment = array_get($context, $segment);
        }

        $url = parse_url($value);

        // Get everything after a possible domain
        // and make sure it starts with a /
        $uris = Stringy::ensureLeft(array_get($url, 'path'), '/');

        //Boom
        $segments = explode('/', $uris);

        return array_get($segments, $segment);
    }

    /**
     * Get the date difference in seconds
     *
     * @param Carbon  $value
     * @param $params
     *
     * @return integer
     */
    public function secondsAgo($value, $params)
    {
        return carbon($value)->diffInSeconds(array_get($params, 0));
    }

    /**
     * Creates a sentence list from the given array and the ability to set the glue
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function sentenceList($value, $params)
    {
        if (! is_array($value)) return $value;

        $glue         = array_get($params, 0, 'and');
        $oxford_comma = array_get($params, 1, true);

        return Helper::makeSentenceList($value, $glue, $oxford_comma);
    }

    /**
     * Because sometimes you just gotta /shrug
     *
     * @param $value
     * @return string
     */
    public function shrug($value)
    {
        return '¯\_(ツ)_/¯';
    }

    /**
     * Shuffles arrays or strings. Multibye friendly.
     *
     * @param $value
     * @return array|string
     */
    public function shuffle($value)
    {
        if (is_array($value)) {
            return collect($value)->shuffle()->all();
        }

        return Stringy::shuffle($value);
    }

    /**
     * Get the singular form of an English word
     *
     * @param $value
     * @param $params
     * @param $context
     * @return string
     */
    public function singular($value)
    {
        return Str::singular($value);
    }

    /**
     * If you don't get it, it wasn't for you.
     *
     * @return string
     */
    public function slackEasterEgg()
    {
        return "Bigfoot was here.";
    }

    /**
     * Converts the string into an URL slug. This includes replacing non-ASCII
     * characters with their closest ASCII equivalents, removing remaining non-ASCII
     * and non-alphanumeric characters, and replacing whitespace with $replacement.
     * The replacement defaults to a single dash, and the string is also
     * converted to lowercase.
     *
     * @param $value
     * @return string
     */
    public function slugify($value)
    {
        return Stringy::slugify($value);
    }

    /**
     * Parse with SmartyPants. Aren't you fancy?
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function smartypants($value, $params)
    {
        return smartypants($value, array_get($params, 0 , 1));
    }

    /**
     * Sort an array by key $params[0] and direction $params[1]
     *
     * @param $value
     * @param $params
     * @return array
     */
    public function sort($value, $params)
    {
        $key = array_get($params, 0);
        $is_descending = strtolower(array_get($params, 1)) == 'desc';

        if ($key === 'random') {
            return $this->shuffle($value);
        }

        // Using sort="true" will allow primitive arrays to be sorted.
        if ($key === 'true') {
            natcasesort($value);
            return $is_descending ? $this->reverse($value) : $value;
        }

        return collect($value)->sortBy($key, SORT_REGULAR, $is_descending)->values()->toArray();
    }

    /**
     * Returns true if the string starts with a given substring ($params[0]), false otherwise.
     * The comparison is case-insensitive.
     *
     * @param $value
     * @param $params
     * @return bool
     */
    public function startsWith($value, $params)
    {
        return Stringy::startsWith($value, array_get($params, 0), false);
    }

    /**
     * Strip tags from a string, allowing for an explicit list. Context aware.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function stripTags($value, $params, $context)
    {
        $tag_var = array_get($params, 0);

        // When used in a macro without specifying any tags, the tag list will just be the boolean
        // value `true`. In that case, we'll use an empty to indicate "all the tags". Otherwise,
        // we'll get the tag list from the context, and then finally just an array of tags.
        if ($tag_var === true) {
            $tags = [];
        } else {
            $tags = ($tag_var) ? array_get($context, $tag_var, $params) : $params;
        }

        return Helper::stripTags($value, (array) $tags);
    }

    /**
     * Subtracts values with the help of science. Context aware.
     *
     * @param $value
     * @param $params
     * @return mixed
     */
    public function subtract($value, $params, $context)
    {
        return $value - $this->getMathModifierNumber($params, $context);
    }

    /**
     * Returns the substring beginning at $start with the specified length.
     * It differs from the mb_substr() function in that providing a length of
     * null will return the rest of the string, rather than an empty string.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function substr($value, $params)
    {
        return Stringy::substr($value, array_get($params, 0), array_get($params, 1));
    }

    /**
     * Returns the sum of all items in the array, optionally by specific key
     * @param $value
     * @param $params
     * @return mixed
     */
    public function sum($value, $params)
    {
        return collect($value)->sum(array_get($params, 0, null));
    }

    /**
     * Surrounds a string with substring $params[0].
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function surround($value, $params)
    {
        return Stringy::surround($value, array_get($params, 0));
    }

    /**
     * Returns a case swapped version of the string.
     *
     * @param $value
     * @return string
     */
    public function swapCase($value)
    {
        return Stringy::swapCase($value);
    }

    /**
     * Convert an array of data from the Table fieldtype into a basic HTML table
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function table($value, $params)
    {
        $rows = $value;
        $parse_markdown = bool(array_get($params, 0));

        $html = '<table>';

        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row['cells'] as $cell) {
                $html .= '<td>';
                $html .= ($parse_markdown) ? markdown($cell) : $cell;
                $html .= '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</table>';

        return $html;
    }

    /**
     * Parse content as Textile.
     *
     * @param $value
     * @return string
     */
    public function textile($value)
    {
        return textile($value);
    }

    /**
     * Returns a string with smart quotes, ellipsis characters, and dashes from
     * Windows-1252 (commonly used in Word documents) replaced by their ASCII equivalents.
     *
     * @param $value
     * @return string
     */
    public function tidy($value)
    {
        return Stringy::tidy($value);
    }

    /**
     * Converts the first character of each word in the string to uppercase.
     *
     * @param $value
     * @return string
     */
    public function title($value)
    {
        $ignore = ['a', 'an', 'the', 'at', 'by', 'for', 'in', 'of', 'on', 'to', 'up', 'and', 'as', 'but', 'or', 'nor'];

        return Stringy::titleize($value, $ignore);
    }

    /**
     * Converts the data to json.
     *
     * @param $value
     * @return string
     */
    public function toJson($value)
    {
        return json_encode($value);
    }

    /**
     * Converts each tab in the string to some number of spaces, as defined by
     * $param[0]. By default, each tab is converted to 4 consecutive spaces.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function toSpaces($value, $params)
    {
        return Stringy::toSpaces($value, array_get($params, 0, 4));
    }

    /**
     * Converts each occurrence of some consecutive number of spaces, as defined by
     * $param[0], to a tab. By default, each 4 consecutive spaces are converted to a tab.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function toTabs($value, $params)
    {
        return Stringy::toTabs($value, array_get($params, 0, 4));
    }

    /**
     * Returns the trimmed string.
     * @param $value
     * @return string
     */
    public function trim($value)
    {
        return Stringy::trim($value);
    }

    /**
     * Truncates the string to a given length ($param[0]). If $param[1] is provided, and
     * truncating occurs, the string is further truncated so that the substring
     * may be appended without exceeding the desired length.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function truncate($value, $params)
    {
        return Stringy::truncate($value, array_get($params, 0), array_get($params, 1, ''));
    }

    /**
     * Applies a timezone to a date
     *
     * Accepts a timezone string as a parameter. If none is provided, then
     * the timezone defined in the system settings will be used.
     *
     * @param  $value
     * @param  $params
     * @return Carbon
     */
    public function timezone($value, $params)
    {
        $timezone = array_get($params, 0, Config::get('system.timezone'));

        return carbon($value)->tz($timezone);
    }

    /**
     * Converts the first character of the supplied string to upper case.
     *
     * @param $value
     * @return string
     */
    public function ucfirst($value)
    {
        return Stringy::upperCaseFirst($value);
    }

    /**
     * Turn an array into an unordered list
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function ul($value, $params)
    {
        return app('html')->ul($value, $this->buildAttributesFromParameters($params));
    }

    /**
     * Decodes URL-encoded string
     *
     * @param $value
     * @return string
     */
    public function urldecode($value)
    {
        return urldecode($value);
    }

    /**
     * URL-encodes string
     *
     * @param $value
     * @return string
     */
    public function urlencode($value)
    {
        return implode('/', array_map('urlencode', explode('/', $value)));
    }

    /**
     * Returns a lowercase and trimmed string separated by underscores.
     * Underscores are inserted before uppercase characters (with the exception
     * of the first character of the string), and in place of spaces as well as dashes.
     *
     * @param $value
     * @return string
     */
    public function underscored($value)
    {
        return Stringy::underscored($value);
    }

    /**
     * Transform a value into uppercase. Multi-byte friendly.
     *
     * @param $value
     * @return string
     */
    public function upper($value)
    {
        return Stringy::toUpperCase($value);
    }

    /**
     * Returns all of the unique-by-key items in the array
     *
     * @param $value
     * @param $params
     * @return static
     */
    public function unique($value, $params)
    {
        return collect($value)->unique(array_get($params, 0))->toArray();
    }

    /**
     * Get the URL from an ID
     *
     * @param $value
     * @return string
     */
    public function url($value)
    {
        if (is_array($value)) {
            $value = array_get($value, 0);
        }

        if (! $item = Asset::find($value)) {
            if (! $item = Content::find($value)) {
                return $value;
            }
        }

        return $item->url();
    }

    /**
     * Get the date difference in weeks
     *
     * @param Carbon  $value
     * @param $params
     *
     * @return integer
     */
    public function weeksAgo($value, $params)
    {
        return carbon($value)->diffInWeeks(array_get($params, 0));
    }

    /**
     * Filters the data by a given key / value pair
     *
     * @param array $value
     * @param $params
     *
     * @return Collection
     */
    public function where($value, $params)
    {
        $key = array_get($params, 0);
        $val = array_get($params, 1);

        $collection = collect($value)->whereLoose($key, $val);

        return $collection->all();
    }

    /**
     * Attempts to prevent widows in a string by adding
     * <nobr> tags between the last two words of each paragraph.
     *
     * @param $value
     * @return string
     */
    public function widont($value)
    {
        return Helper::widont($value);
    }

    /**
     * Wraps an HTML tag around the value
     *
     * @param $value
     * @return string
     */
    public function wrap($value, $params)
    {
        $attributes = '';
        $tag = array_get($params, 0);

        // Emmet-esque classes
        // You may specify "tag.class.class.class" etc.
        if (Str::contains($tag, '.')) {
            list($tag, $classes) = explode('.', $tag, 2);
            $attributes = sprintf(' class="%s"', str_replace('.', ' ', $classes));
        }

        return "<{$tag}{$attributes}>$value</$tag>";
    }

    /**
     * Count the number of words in a string
     *
     * @param $value
     * @return mixed
     */
    public function wordCount($value)
    {
        return str_word_count($value);
    }

    /**
     * Get the date difference in years
     *
     * @param Carbon  $value
     * @param $params
     *
     * @return integer
     */
    public function yearsAgo($value, $params)
    {
        return carbon($value)->diffInYears(array_get($params, 0));
    }

    // ------------------------------------

    /**
     * Takes a modifier array, split on ":", and formats it for HTML attribute key:value pairs
     *
     * @param $params
     * @return array
     */
    private function buildAttributesFromParameters($params, $delimiter = ':')
    {
        $attributes = [];
        foreach ($params as $param) {
            list($key, $value) = explode($delimiter, $param);
            $attributes[$key] = $value;
        }

        return $attributes;
    }

    private function getMathModifierNumber($params, $context)
    {
        $number = $params[0];

        // If the number is already a number, use that. Otherwise, attempt to resolve it
        // from a value in the context. This allows users to specify a variable name.
        return (is_numeric($number))
            ? $number
            : array_get($context, $number, $number);
    }
}
