<?php

namespace Statamic\Modifiers;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Facades\Asset;
use Statamic\Facades\Config;
use Statamic\Facades\Data;
use Statamic\Facades\File;
use Statamic\Facades\Markdown;
use Statamic\Facades\Parse;
use Statamic\Facades\Path;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\Facades\YAML;
use Statamic\Fields\Value;
use Statamic\Support\Arr;
use Statamic\Support\Html;
use Statamic\Support\Str;
use Stringy\StaticStringy as Stringy;

class CoreModifiers extends Modifier
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
     * Adds a query param matching the specified key/value pair.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function addQueryParam($value, $params)
    {
        if (isset($params[0])) {
            // Remove anchor from the URL.
            $url = strtok($value, '#');

            // Get the anchor value an preprend it with a "#" if a value is retrieved.
            $fragment = parse_url($value, PHP_URL_FRAGMENT);
            $anchor = is_null($fragment) ? '' : "#{$fragment}";

            // If a "?" is present in the URL, it means we should prepend "&" to the query param. Else, prepend "?".
            $character = (strpos($value, '?') !== false) ? '&' : '?';

            // Build the query param. If the second param is not set, just set the value as empty.
            $queryParam = "{$params[0]}=".($params[1] ?? '');

            $value = "{$url}{$character}{$queryParam}{$anchor}";
        }

        return $value;
    }

    /**
     * Creates a sentence list from the given array and the ability to set the glue.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function ampersandList($value, $params)
    {
        if (! is_array($value)) {
            return $value;
        }

        $glue = Arr::get($params, 0, '&');
        $oxford_comma = Arr::get($params, 1, false);

        return Str::makeSentenceList($value, $glue, $oxford_comma);
    }

    /**
     * Alias an array variable.
     *
     * @param $value
     * @param $params
     * @return array|void
     */
    public function alias($value, $params)
    {
        if (! is_array($value)) {
            return;
        }

        $as = Arr::get($params, 0);

        return [$as => $value];
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
        return Stringy::at($value, Arr::get($params, 0));
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
     * Removes a given number ($param[0]) of characters from the end of a variable.
     *
     * @param $value
     * @param $params
     * @return array|false|string
     */
    public function backspace($value, $params)
    {
        if (is_array($value) || ! isset($params[0]) || ! is_numeric($params[0]) || $params[0] < 0) {
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
     * Wraps a value in CDATA tags for RSS/XML feeds.
     *
     * @param $value
     * @return string
     */
    public function cdata($value)
    {
        return '<![CDATA['.$value.']]>';
    }

    /**
     * Rounds a number up to the next whole number.
     *
     * @param $value
     * @return int
     */
    public function ceil($value)
    {
        return ceil((float) $value);
    }

    /**
     * Breaks arrays or collections into smaller ones of a given size.
     *
     * @param $value
     * @return array
     */
    public function chunk($value, $params)
    {
        return collect($value)
            ->chunk(Arr::get($params, 0))
            ->map(function ($chunk) {
                return ['chunk' => $chunk];
            })
            ->all();
    }

    /**
     * Collapses an array of arrays into a flat array.
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
     * Debug a value with a call to JavaScript's console.log.
     *
     * @param  $value
     * @return string
     */
    public function consoleLog($value)
    {
        return '<script>
            window.log=function(a){if(this.console){console.log(a);}};
            log('.json_encode($value).');
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
        $needle = Arr::get($context, $params[0], $params[0]);

        if (is_array($haystack)) {
            if (Arr::isAssoc($haystack)) {
                return Arr::exists($haystack, $needle);
            }

            return in_array($needle, $haystack);
        }

        return Stringy::contains($haystack, $needle, Arr::get($params, 1, false));
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
        $needles = Arr::get($context, $params[0], $params);

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
     * Returns the number of items in an array.
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
        return Stringy::countSubstr($value, Arr::get($params, 0), Arr::get($params, 1, false));
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
     * Get the date difference in days.
     *
     * @param Carbon  $value
     * @param $params
     *
     * @return int
     */
    public function daysAgo($value, $params)
    {
        return $this->carbon($value)->diffInDays(Arr::get($params, 0));
    }

    /**
     * Dump, Die, and Debug using Ignition.
     *
     * @param $value
     */
    public function ddd($value)
    {
        ddd($value);
    }

    /**
     * Dump a var into the Debug bar for data exploration.
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
        return Html::decode($value);
    }

    /**
     * Replaces hyphens and underscores with spaces.
     *
     * @param $value
     * @return string
     */
    public function deslugify($value)
    {
        return trim(preg_replace('~[-_]~', ' ', $value), ' ');
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
     * Turn an array into an definition list.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function dl($value, $params)
    {
        return Html::dl($value, $this->buildAttributesFromParameters($params));
    }

    /**
     * Dump and die the output of a variable.
     *
     * @param $value
     */
    public function dd($value)
    {
        function_exists('ddd') ? ddd($value) : dd($value);
    }

    /**
     * Dump a variable.
     *
     * @param $value
     */
    public function dump($value)
    {
        dump($value);
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
        return Stringy::endsWith($value, Arr::get($params, 0), false);
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
        return Stringy::ensureLeft($value, Arr::get($params, 0));
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
        return Stringy::ensureRight($value, Arr::get($params, 0));
    }

    /**
     * Convert an HTML string to entities.
     *
     * @param  string  $value
     * @return string
     */
    public function entities($value)
    {
        return Html::entities($value);
    }

    /**
     * Breaks a string at a given marker.
     * Uses <!--more--> by default.
     *
     * @param $value
     * @param $params
     * @return array|false|string
     */
    public function excerpt($value, $params)
    {
        if (is_array($value)) {
            return $value;
        }

        $breaker = Arr::get($params, 0, '<!--more-->');

        return strstr($value, $breaker, true);
    }

    /**
     * Just like the PHP method, breaks a string into an array on a specified key, $params[0].
     *
     * @param $value
     * @param $params
     * @return array
     */
    public function explode($value, $params)
    {
        return explode(Arr::get($params, 0), $value);
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
        return Html::favicon($value, $this->buildAttributesFromParameters($params));
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
            return Arr::get($value, 0);
        }

        return Stringy::first($value, Arr::get($params, 0));
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
     * Swaps the keys with their corresponding values.
     *
     * @param $value
     * @return array
     */
    public function flip($value)
    {
        return array_flip($value);
    }

    /**
     * Rounds a number down to the next whole number.
     *
     * @param $value
     * @return int
     */
    public function floor($value)
    {
        return floor((float) $value);
    }

    /**
     * Converts a string to a Carbon instance and formats it according to the whim of the Overlord.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function format($value, $params)
    {
        return $this->carbon($value)->format(Arr::get($params, 0));
    }

    /**
     * Converts a string to a Carbon instance and formats it according to the whim of the Overlord.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function formatLocalized($value, $params)
    {
        return $this->carbon($value)->formatLocalized(Arr::get($params, 0));
    }

    /**
     * Format a number with grouped thousands and decimal points.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function formatNumber($value, $params)
    {
        $precision = Arr::get($params, 0, 0);
        $dec_point = Arr::get($params, 1, '.');
        $thousands_sep = Arr::get($params, 2, ',');

        $number = floatval(str_replace(',', '', $value));

        return number_format($number, $precision, $dec_point, $thousands_sep);
    }

    /**
     * Replace /absolute/urls with http://domain.com/urls.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function fullUrls($value, $params)
    {
        $domain = Site::current()->absoluteUrl();

        return preg_replace_callback('/="(\/[^"]+)"/ism', function ($item) use ($domain) {
            return '="'.Path::tidy($domain.$item[1]).'"';
        }, $value);
    }

    /**
     * Get any variable from a relationship.
     *
     * @param $value
     * @return string
     */
    public function get($value, $params)
    {
        // If the requested value is an array, we'll just grab the first one.
        if (is_array($value)) {
            $value = Arr::get($value, 0);
        }

        // If it's not already an object, we'll assume we have an ID and get that.
        $item = is_object($value) ? $value : Data::find($value);

        // No item? We'll just spit the value back as-is. This seems like a sensible solution here.
        if (! $item) {
            return $value;
        }

        // Get the requested variable, which is the first parameter.
        $var = Arr::get($params, 0);

        // Convert the item to an array, since we'll want access to all the
        // available data. Then grab the requested variable from there.
        $array = $item instanceof Augmentable ? $item->toAugmentedArray() : $item->toArray();

        if ($arrayValue = Arr::get($array, $var)) {
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
     * Get a Gravatar image URL from an email.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function gravatar($value, $params)
    {
        return URL::gravatar($value, Arr::get($params, 0));
    }

    /**
     * Groups the collection's items by a given key.
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
     * Get the date difference in hours.
     *
     * @param Carbon  $value
     * @param $params
     *
     * @return int
     */
    public function hoursAgo($value, $params)
    {
        return $this->carbon($value)->diffInHours(Arr::get($params, 0));
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
        return '<img src="'.$value.'"'.Html::attributes($this->buildAttributesFromParameters($params)).'>';
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
        if (is_null($value)) {
            return '';
        }

        // Workaround to support pipe characters. If there are multiple params
        // that means a pipe was used. We'll just join them for now.
        if (count($params) > 1) {
            $params = [implode('|', $params)];
        }

        return implode(Arr::get($params, 0, ', '), $value);
    }

    /**
     * Check if an item exists in an array using "dot" notation.
     *
     * @param $value
     * @param $params
     * @return bool
     */
    public function inArray($haystack, $params, $context)
    {
        if (! is_array($haystack)) {
            return false;
        }

        $needle = Arr::get($context, $params[0], $params);

        if (is_array($needle) && count($needle) === 1) {
            $needle = $needle[0];
        }

        if (Arr::isAssoc($haystack)) {
            return Arr::exists($haystack, $needle);
        }

        return in_array($needle, $haystack);
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
        $substring = Arr::get($params, 0);
        $position = Arr::get($params, 1);

        return Stringy::insert($value, $substring, $position);
    }

    /**
     * Determines if the date is after another specified date ($params[0]).
     *
     * @param $value
     * @param $params
     * @param $context
     * @return bool
     */
    public function isAfter($value, $params, $context)
    {
        $date = $this->carbon(Arr::get($context, $params[0], $params[0]));

        return $this->carbon($value)->gt($date);
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
     * Returns true if the value is an array.
     *
     * @param $value
     * @return bool
     */
    public function isArray($value)
    {
        return is_array($value);
    }

    /**
     * Determines if the date is before another specified date ($params[0]).
     *
     * @param $value
     * @param $params
     * @param $context
     * @return bool
     */
    public function isBefore($value, $params, $context)
    {
        $date = $this->carbon(Arr::get($context, $params[0], $params[0]));

        return $this->carbon($value)->lt($date);
    }

    /**
     * Determines if the date is between two other specified dates, $params[0] and $params[1].
     *
     * @param $value
     * @param $params
     * @param $context
     * @return bool
     */
    public function isBetween($value, $params, $context)
    {
        $date1 = $this->carbon(Arr::get($context, $params[0], $params[0]));
        $date2 = $this->carbon(Arr::get($context, $params[1], $params[1]));

        return $this->carbon($value)->between($date1, $date2);
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
     * Return true if the string is an email address.
     *
     * @param $value
     * @return bool
     */
    public function isEmail($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Checks to see if an array is empty. Like, for realsies.
     *
     * @param $value
     * @return bool
     */
    public function isEmpty($value)
    {
        if (is_array($value)) {
            foreach ($value as $subvalue) {
                if (! $this->isEmpty($subvalue)) {
                    return false;
                }
            }
        } elseif (! empty($value) || $value !== '') {
            return false;
        }

        return true;
    }

    /**
     * Determines if the date is in the future, ie. greater (after) than now.
     *
     * @param $value
     * @return bool
     */
    public function isFuture($value)
    {
        return $this->carbon($value)->isFuture();
    }

    /**
     * Returns true if the value is iterable.
     *
     * @param $value
     * @return bool
     */
    public function isIterable($value)
    {
        return is_iterable($value);
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
     * Determines if the date in a leap year.
     *
     * @param $value
     * @return bool
     */
    public function isLeapYear($value)
    {
        return $this->carbon($value)->isLeapYear();
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
     * Finds whether a value is a number or a numeric string.
     *
     * @param $value
     * @return bool
     */
    public function isNumeric($value)
    {
        return is_numeric($value);
    }

    /**
     * Determines if the date is in the past, ie. less (before) than now.
     *
     * @param $value
     * @return bool
     */
    public function isPast($value)
    {
        return $this->carbon($value)->isPast();
    }

    /**
     * Determines if the date is today.
     *
     * @param $value
     * @return bool
     */
    public function isToday($value)
    {
        return $this->carbon($value)->isToday();
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
     * Returns true if the string is a URL.
     *
     * @param $value
     * @return bool
     */
    public function isUrl($value)
    {
        return Str::isUrl($value);
    }

    /**
     * Determines if the date on a weekday.
     *
     * @param $value
     * @return bool
     */
    public function isWeekday($value)
    {
        return $this->carbon($value)->isWeekday();
    }

    /**
     * Determines if the date on a weekend.
     *
     * @param $value
     * @return bool
     */
    public function isWeekend($value)
    {
        return $this->carbon($value)->isWeekend();
    }

    /**
     * Determines if the date is yesterday.
     *
     * @param $value
     * @return bool
     */
    public function isYesterday($value)
    {
        return $this->carbon($value)->isYesterday();
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

        return Stringy::last($value, Arr::get($params, 0));
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
     * Get the items in an array or characters in a string.
     *
     * @param $value
     * @return int
     */
    public function length($value)
    {
        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        }

        return (is_array($value)) ? count($value) : Stringy::length($value);
    }

    /**
     * Limit the number of items in an array.
     *
     * @param $value
     * @param $params
     * @return array|Collection
     */
    public function limit($value, $params)
    {
        $limit = Arr::get($params, 0, 0);

        if ($value instanceof Collection) {
            return $value->take($limit);
        }

        return array_slice($value, 0, $limit);
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

        return Html::link($value, $title, $attributes);
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
     * Replace a var with a localized string.
     *
     * @param $value
     * @return string
     */
    public function localize($value)
    {
        return $this->trans($value);
    }

    /**
     * Rough macro prototype that only uses CoreModifiers.
     *
     * @param $value
     * @param $params
     * @param $context
     * @return mixed
     */
    public function macro($value, $params, $context)
    {
        $path = base_path('resources/macros.yaml');
        $macros = YAML::file($path)->parse();
        $macro = Arr::get($macros, Arr::get($params, 0));

        return collect($macro)->map(function ($params, $name) {
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
        return Html::mailto($value, null, $this->buildAttributesFromParameters($params));
    }

    /**
     * Parse content as Markdown.
     *
     * @param $value
     * @return mixed
     */
    public function markdown($value, $params)
    {
        if (! is_string($value)) {
            return $value;
        }

        $parser = $params[0] ?? 'default';

        if (in_array($parser, [true, 'true', ''])) {
            $parser = 'default';
        }

        return Markdown::parser($parser)->parse($value);
    }

    /**
     * Merge an array variable with another array variable.
     *
     * @param $value
     * @param $params
     * @param $context
     * @return array
     */
    public function merge($value, $params, $context)
    {
        $to_merge = (array) Arr::get($context, $params[0], $context);

        return array_merge($value, $to_merge);
    }

    /**
     * Generate an md5 hash of a value.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function md5($value)
    {
        return md5($value);
    }

    /**
     * Get the date difference in minutes.
     *
     * @param Carbon  $value
     * @param $params
     *
     * @return int
     */
    public function minutesAgo($value, $params)
    {
        return $this->carbon($value)->diffInMinutes(Arr::get($params, 0));
    }

    /**
     * Performs modulus division on a value. Context aware.
     *
     * @param $value
     * @param $params
     * @param $context
     * @return int
     */
    public function mod($value, $params, $context)
    {
        $number = Arr::get($context, $params[0], $params[0]);

        return $value % $number;
    }

    /**
     * Alters the timestamp by incrementing or decremting in a format acceted by strtotime().
     *
     * @link http://php.net/manual/en/function.strtotime.php
     *
     * @param $value
     * @param $params
     * @return \DateTime
     */
    public function modifyDate($value, $params)
    {
        return $this->carbon($value)->modify(Arr::get($params, 0));
    }

    /**
     * Get the date difference in months.
     *
     * @param Carbon  $value
     * @param $params
     *
     * @return int
     */
    public function monthsAgo($value, $params)
    {
        return $this->carbon($value)->diffInMonths(Arr::get($params, 0));
    }

    /**
     * Multiplies values together with a little help from science. Context aware.
     *
     * @param $value
     * @param $params
     * @param $context
     * @return float|int
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
        return $value.' is pretty neat!';
    }

    /**
     * Replaces line breaks with <br> tags.
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
        return Html::obfuscate($value);
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
        return Html::email($value, null, $this->buildAttributesFromParameters($params));
    }

    /**
     * Turn an array into an ordered list.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function ol($value, $params)
    {
        return Html::ol($value, $this->buildAttributesFromParameters($params));
    }

    /**
     * Turn an array into a pipe delimited list.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function optionList($value, $params)
    {
        if (count($params) > 1) {
            $params = [implode('|', $params)];
        }

        return implode(Arr::get($params, 0, '|'), $value);
    }

    /**
     * Offset the items in an array.
     *
     * @param $value
     * @param $params
     * @return array|Collection
     */
    public function offset($value, $params)
    {
        $isArray = is_array($value);

        $value = collect($value)->slice(Arr::get($params, 0, 0))->values();

        return $isArray ? $value->all() : $value;
    }

    /**
     * Get the output of an Asset, useful for SVGs.
     *
     * @param $value
     * @return array
     */
    public function output($value)
    {
        if (! is_string($value) && ! $value instanceof AssetContract) {
            return $value;
        }

        $asset = Asset::find($value);

        if ($asset) {
            return $asset->disk()->get($asset->path());
        }
    }

    /**
     * Renders an array variable with a partial, context aware.
     *
     * @param $value
     * @param $params
     * @param $context
     * @return string
     */
    public function partial($value, $params, $context)
    {
        $name = Arr::get($context, $params[0], $params[0]);

        $partial = 'partials/'.$name.'.html';

        return Parse::template(File::disk('resources')->get($partial), $value);
    }

    /**
     * Get the plural form of an English word with access to $context.
     *
     * @param $value
     * @param $params
     * @param $context
     * @return string
     */
    public function plural($value, $params, $context)
    {
        $count = Arr::get($params, 0);

        if (! is_numeric($count)) {
            $count = (int) Arr::get($context, $count);
        }

        return Str::plural($value, $count);
    }

    /**
     * Return a random value from an array.
     *
     * @param $value
     * @return string
     */
    public function random($value)
    {
        return array_random($value);
    }

    /**
     * URL-encode according to RFC 3986.
     *
     * @param $value
     * @return string
     */
    public function rawurlencode($value)
    {
        return implode('/', array_map('rawurlencode', explode('/', $value)));
    }

    /**
     * Send data to Laravel Ray.
     *
     * @param $value
     * @return void
     */
    public function ray($value)
    {
        throw_unless(function_exists('ray'), new \Exception('Ray is not installed. Run `composer require spatie/laravel-ray --dev`'));

        ray($value);

        return $value;
    }

    /**
     * Estimate the read time based on a given number of words per minute.
     *
     * @param $value
     * @param $params
     * @return int
     */
    public function readTime($value, $params)
    {
        $words = $this->wordCount(strip_tags($value));

        return ceil($words / Arr::get($params, 0, 200));
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
        return Stringy::regexReplace($value, Arr::get($params, 0), Arr::get($params, 1));
    }

    /**
     * Alias of `diff_for_humans`.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function relative($value, $params)
    {
        return $this->diffForHumans($value, $params);
    }

    /**
     * Format date in an easier for humans to read format.
     * Send $params[1] as true to turn off modifiers "ago", "from now", etc.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function diffForHumans($value, $params)
    {
        $remove_modifiers = Arr::get($params, 0, false);

        return $this->carbon($value)->diffForHumans(null, $remove_modifiers);
    }

    /**
     * Format date in an easier for owls to read format.
     * For whoever gives a hoot.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function diffForOwls($value, $params)
    {
        return strrev($this->diffForHumans($value, $params));
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
        return Stringy::removeLeft($value, Arr::get($params, 0));
    }

    /**
     * Removes a query param matching the specified key if it exists.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function removeQueryParam($value, $params)
    {
        if (isset($params[0])) {
            // Remove query params (and any following anchor) from the URL.
            $url = strtok($value, '?');
            $url = strtok($url, '#');

            // Parse the URL to retrieve the possible query string and anchor.
            $parsedUrl = parse_url($value);

            // Get the anchor value an preprend it with a "#" if a value is retrieved.
            $anchor = isset($parsedUrl['fragment']) ? "#{$parsedUrl['fragment']}" : '';

            // Build an associative array based on the query string.
            parse_str($parsedUrl['query'] ?? '', $queryAssociativeArray);

            // Remove the query param matching the specified key.
            unset($queryAssociativeArray[$params[0]]);

            $value = $url.(empty($queryAssociativeArray) ? '' : '?'.http_build_query($queryAssociativeArray)).$anchor;
        }

        return $value;
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
        return Stringy::removeRight($value, Arr::get($params, 0));
    }

    /**
     * Repeats value a given number of times.
     *
     * @param $value
     * @param $params
     * @param $context
     * @return string
     */
    public function repeat($value, $params, $context)
    {
        $times = Arr::get($params, 0, 1);
        $times = is_numeric($times) ? $times : Arr::get($context, $times);

        $times = ($times instanceof Value) ? $times->value() : $times;

        return str_repeat($value, $times);
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
        return Stringy::replace($value, Arr::get($params, 0), Arr::get($params, 1));
    }

    /**
     * Reverses the order of a string or list.
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
     * Rounds a number to a specified precision (number of digits after the decimal point).
     * @param $value
     * @param $params
     * @return float
     */
    public function round($value, $params)
    {
        return round($value, (int) Arr::get($params, 0, 0));
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
        return Stringy::safeTruncate($value, Arr::get($params, 0, 200), Arr::get($params, 1, ''));
    }

    /**
     * Convert special characters to HTML entities with htmlspecialchars.
     *
     * @param $value
     * @return string
     */
    public function sanitize($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, Config::get('statamic.system.charset', 'UTF-8'), false);
    }

    /**
     * Place variables in a scope.
     *
     * @param  $value
     * @param  $params
     * @return array
     */
    public function scope($value, $params)
    {
        if (! $scope = Arr::get($params, 0)) {
            throw new \Exception('Scope modifier requires a name.');
        }

        if ($value instanceof Collection) {
            $value = $value->toAugmentedArray();
        }

        return Arr::addScope($value, $scope);
    }

    /**
     * Returns a segment by number from any valid URL or UI.
     *
     * @param  $value
     * @param  $params
     * @param  $context
     * @return string
     */
    public function segment($value, $params, $context)
    {
        // Which segment?
        $segment = Arr::get($params, 0, 1);

        // Support a variable name
        if (! is_numeric($segment)) {
            $segment = Arr::get($context, $segment);
        }

        $url = parse_url($value);

        // Get everything after a possible domain
        // and make sure it starts with a /
        $uris = Stringy::ensureLeft(Arr::get($url, 'path'), '/');

        //Boom
        $segments = explode('/', $uris);

        return Arr::get($segments, $segment);
    }

    /**
     * Get the date difference in seconds.
     *
     * @param Carbon  $value
     * @param $params
     * @return int
     */
    public function secondsAgo($value, $params)
    {
        return $this->carbon($value)->diffInSeconds(Arr::get($params, 0));
    }

    /**
     * Creates a sentence list from the given array and the ability to set the glue.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function sentenceList($value, $params)
    {
        if (! is_array($value)) {
            return $value;
        }

        $glue = Arr::get($params, 0, __('and'));
        $oxford_comma = Arr::get($params, 1, true);

        return Str::makeSentenceList($value, $glue, $oxford_comma);
    }

    /**
     * Sets a query param matching the specified key/value pair.
     * If the key exists, its value gets updated. Else, the key/value pair gets added.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function setQueryParam($value, $params)
    {
        if (isset($params[0])) {
            // Remove query params (and any following anchor) from the URL.
            $url = strtok($value, '?');
            $url = strtok($url, '#');

            // Parse the URL to retrieve the possible query string and anchor.
            $parsedUrl = parse_url($value);

            // Get the anchor value an preprend it with a "#" if a value is retrieved.
            $anchor = isset($parsedUrl['fragment']) ? "#{$parsedUrl['fragment']}" : '';

            // Build an associative array based on the query string.
            parse_str($parsedUrl['query'] ?? '', $queryAssociativeArray);

            // Update the existing param that matches the specified key, or add it if it doesn't exist.
            $queryAssociativeArray[$params[0]] = $params[1] ?? '';

            $value = "{$url}?".http_build_query($queryAssociativeArray).$anchor;
        }

        return $value;
    }

    /**
     * Because sometimes you just gotta /shrug.
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

        if ($value instanceof Collection) {
            return $value->shuffle();
        }

        return Stringy::shuffle($value);
    }

    /**
     * Get the singular form of an English word.
     *
     * @param $value
     * @return string
     */
    public function singular($value)
    {
        return Str::singular($value);
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
        return Stringy::slugify($value, '-', Config::getShortLocale());
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
        return Html::smartypants($value);
    }

    /**
     * Sort an array by key $params[0] and direction $params[1].
     *
     * @param $value
     * @param $params
     * @return array
     */
    public function sort($value, $params)
    {
        $key = Arr::get($params, 0, 'true');
        $desc = strtolower(Arr::get($params, 1)) == 'desc';

        $value = $value instanceof Collection ? $value : collect($value);

        if ($key === 'random') {
            return $value->shuffle();
        }

        if ($key === 'true') {
            $value = $desc ? $value->sort()->reverse() : $value->sort();
        } else {
            $value = $desc ? $value->sortByDesc($key) : $value->sortBy($key);
        }

        return $value->values();
    }

    /**
     * Strip whitespace from HTML.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function spaceless($value, $params)
    {
        $nolb = str_replace(["\r", "\n"], '', $value);
        $nospaces = preg_replace('/\s+/', ' ', $nolb);

        return preg_replace('/>\s+</', '><', $nospaces);
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
        return Stringy::startsWith($value, Arr::get($params, 0), false);
    }

    /**
     * Strip tags from a string, allowing for an explicit list. Context aware.
     *
     * @param $value
     * @param $params
     * @param $context
     * @return string
     */
    public function stripTags($value, $params, $context)
    {
        $tag_var = Arr::get($params, 0);

        // When used in a macro without specifying any tags, the tag list will just be the boolean
        // value `true`. In that case, we'll use an empty to indicate "all the tags". Otherwise,
        // we'll get the tag list from the context, and then finally just an array of tags.
        if ($tag_var === true) {
            $tags = [];
        } else {
            $tags = ($tag_var) ? Arr::get($context, $tag_var, $params) : $params;
        }

        return Str::stripTags($value, (array) $tags);
    }

    /**
     * Subtracts values with the help of science. Context aware.
     *
     * @param $value
     * @param $params
     * @param $context
     * @return int|float
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
        return Stringy::substr($value, Arr::get($params, 0), Arr::get($params, 1));
    }

    /**
     * Returns the sum of all items in the array, optionally by specific key.
     * @param $value
     * @param $params
     * @return mixed
     */
    public function sum($value, $params)
    {
        $key = Arr::get($params, 0, null);

        return collect($value)->reduce(function ($carry, $value) use ($key) {
            if ($key) {
                $value = data_get($value, $key);
            }

            $value = $value instanceof Value ? $value->value() : $value;

            return $carry + (int) $value;
        }, 0);
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
        return Stringy::surround($value, Arr::get($params, 0));
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
     * Convert an array of data from the Table fieldtype into a basic HTML table.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function table($value, $params)
    {
        $rows = $value;
        $parse_markdown = in_array(Arr::get($params, 0), ['true', 'markdown']);

        $html = '<table>';

        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row['cells'] as $cell) {
                $html .= '<td>';
                $html .= ($parse_markdown) ? Html::markdown($cell) : $cell;
                $html .= '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</table>';

        return $html;
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
     * @param $params
     * @return string
     */
    public function toJson($value, $params)
    {
        $options = Arr::get($params, 0) === 'pretty' ? JSON_PRETTY_PRINT : null;

        if ($value instanceof Collection || $value instanceof Augmentable) {
            $value = $value->toAugmentedArray();
        }

        return json_encode($value, $options);
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
        return Stringy::toSpaces($value, Arr::get($params, 0, 4));
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
        return Stringy::toTabs($value, Arr::get($params, 0, 4));
    }

    /**
     * Translates a string.
     *
     * @param $value
     * @return string
     */
    public function trans($value)
    {
        return trans($value);
    }

    /**
     * Translates and pluralizes a string.
     *
     * @param $value
     * @param $params
     * @param $context
     * @return string
     */
    public function transChoice($value, $params, $context)
    {
        $count = Arr::get($context, $params[0], $params[0]);

        return trans_choice($value, $count);
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
        return Stringy::truncate($value, Arr::get($params, 0), Arr::get($params, 1, ''));
    }

    /**
     * Converts a Carbon instance to a timestamp.
     *
     * @param  $value
     * @param  $params
     * @return int
     */
    public function timestamp($value)
    {
        return $value->timestamp;
    }

    /**
     * Applies a timezone to a date.
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
        $timezone = Arr::get($params, 0, Config::get('app.timezone'));

        return $this->carbon($value)->tz($timezone);
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
     * Turn an array into an unordered list.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function ul($value, $params)
    {
        return Html::ul($value, $this->buildAttributesFromParameters($params));
    }

    /**
     * Decodes URL-encoded string.
     *
     * @param $value
     * @return string
     */
    public function urldecode($value)
    {
        return urldecode($value);
    }

    /**
     * URL-encodes string.
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
     * Returns all of the unique-by-key items in the array.
     *
     * @param $value
     * @param $params
     * @return static
     */
    public function unique($value, $params)
    {
        return collect($value)->unique(Arr::get($params, 0))->toArray();
    }

    /**
     * Get the URL from an ID.
     *
     * @param $value
     * @return string
     */
    public function url($value)
    {
        if (is_array($value)) {
            $value = Arr::get($value, 0);
        }

        $item = is_string($value) ? optional(Data::find($value)) : $value;

        return $item->url();
    }

    /**
     * Get the date difference in weeks.
     *
     * @param Carbon  $value
     * @param $params
     *
     * @return int
     */
    public function weeksAgo($value, $params)
    {
        return $this->carbon($value)->diffInWeeks(Arr::get($params, 0));
    }

    /**
     * Filters the data by a given key / value pair.
     *
     * @param array $value
     * @param $params
     * @return array
     */
    public function where($value, $params)
    {
        $key = Arr::get($params, 0);
        $val = Arr::get($params, 1);

        $collection = collect($value)->where($key, $val);

        return $collection->values()->all();
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
        return Str::widont($value);
    }

    /**
     * Wraps an HTML tag around the value.
     *
     * @param $value
     * @return string
     */
    public function wrap($value, $params)
    {
        if (! $value) {
            return $value;
        }

        $attributes = '';
        $tag = Arr::get($params, 0);

        // Emmet-esque classes
        // You may specify "tag.class.class.class" etc.
        if (Str::contains($tag, '.')) {
            [$tag, $classes] = explode('.', $tag, 2);
            $attributes = sprintf(' class="%s"', str_replace('.', ' ', $classes));
        }

        return "<{$tag}{$attributes}>$value</$tag>";
    }

    /**
     * Count the number of words in a string.
     *
     * @param $value
     * @return mixed
     */
    public function wordCount($value)
    {
        // adapted mb_str_word_count from https://stackoverflow.com/a/17725577
        $words = empty($string = trim($value)) ? [] : preg_split('~[^\p{L}\p{N}\']+~u', $value);

        return count($words);
    }

    /**
     * Get the date difference in years.
     *
     * @param Carbon  $value
     * @param $params
     *
     * @return int
     */
    public function yearsAgo($value, $params)
    {
        return $this->carbon($value)->diffInYears(Arr::get($params, 0));
    }

    /**
     * Get the embed URL when given a youtube or vimeo link that's
     * direct to the page.
     *
     * @param string  $url
     * @return string
     */
    public function embedUrl($url)
    {
        if (Str::contains($url, 'vimeo')) {
            $url = str_replace('/vimeo.com', '/player.vimeo.com/video', $url);

            if (Str::contains($url, '?')) {
                $url = str_replace('?', '?dnt=1&', $url);
            } else {
                $url .= '?dnt=1';
            }

            return $url;
        }

        if (Str::contains($url, 'youtu.be')) {
            $url = str_replace('youtu.be', 'www.youtube.com/embed', $url);

            // Check for start at point and replace it with correct parameter.
            if (Str::contains($url, '?t=')) {
                $url = str_replace('?t=', '?start=', $url);
            }
        }

        if (Str::contains($url, 'youtube.com/watch?v=')) {
            $url = str_replace('watch?v=', 'embed/', $url);
        }

        if (Str::contains($url, 'youtube.com')) {
            $url = str_replace('youtube.com', 'youtube-nocookie.com', $url);
        }

        return $url;
    }

    /**
     * Whether a given video URL is embeddable.
     *
     * @param string $url
     * @return bool
     */
    public function isEmbeddable($url)
    {
        return Str::contains($url, ['youtu.be', 'youtube', 'vimeo']);
    }

    /**
     * Converts a string to a Carbon instance and formats it with ISO formats.
     *
     * @param $value
     * @param $params
     * @return string
     */
    public function isoFormat($value, $params)
    {
        return $this->carbon($value)->isoFormat(Arr::get($params, 0));
    }

    // ------------------------------------

    /**
     * Takes a modifier array, split on ":", and formats it for HTML attribute key:value pairs.
     *
     * @param $params
     * @param string $delimiter
     * @return array
     */
    private function buildAttributesFromParameters($params, $delimiter = ':')
    {
        if (empty(array_filter($params))) {
            return [];
        }

        $attributes = [];
        foreach ($params as $param) {
            [$key, $value] = explode($delimiter, $param);
            $attributes[$key] = $value;
        }

        return $attributes;
    }

    private function getMathModifierNumber($params, $context)
    {
        $number = $params[0];

        // If the number is already a number, use that. Otherwise, attempt to resolve it
        // from a value in the context. This allows users to specify a variable name.
        $number = (is_numeric($number))
            ? $number
            : Arr::get($context, $number, $number);

        return ($number instanceof Value) ? $number->value() : $number;
    }

    private function carbon($value)
    {
        if (! $value instanceof Carbon) {
            $value = (is_numeric($value)) ? Carbon::createFromTimestamp($value) : Carbon::parse($value);
        }

        return $value;
    }
}
