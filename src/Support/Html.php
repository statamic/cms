<?php

namespace Statamic\Support;

use Closure;
use Illuminate\Support\HtmlString;
use Michelf\SmartyPants;
use Statamic\Facades\Config;
use Statamic\Facades\Markdown;

class Html
{
    /**
     * Build an HTML attribute string from an array.
     *
     * @param  array  $attributes
     * @return string
     */
    public static function attributes($attributes)
    {
        $html = [];

        foreach ((array) $attributes as $key => $value) {
            $element = static::attributeElement($key, $value);

            if (! is_null($element)) {
                $html[] = $element;
            }
        }

        return count($html) > 0 ? ' '.implode(' ', $html) : '';
    }

    /**
     * Build a single attribute element.
     *
     * @param  string  $key
     * @param  string  $value
     * @return string
     */
    protected static function attributeElement($key, $value)
    {
        // For numeric keys we will assume that the value is a boolean attribute
        // where the presence of the attribute represents a true value and the
        // absence represents a false value.
        // This will convert HTML attributes such as "required" to a correct
        // form instead of using incorrect numerics.
        if (is_numeric($key)) {
            return $value;
        }

        // Treat boolean attributes as HTML properties
        if (is_bool($value) && $key !== 'value') {
            return $value ? $key : '';
        }

        if (is_array($value) && $key === 'class') {
            return 'class="'.implode(' ', $value).'"';
        }

        if (! is_null($value)) {
            return $key.'="'.e($value, false).'"';
        }
    }

    /**
     * Transform the string to an Html serializable object.
     *
     * @param $html
     * @return \Illuminate\Support\HtmlString
     */
    protected static function toHtmlString($html)
    {
        return new HtmlString($html);
    }

    /**
     * Convert entities to HTML characters.
     *
     * @param  string  $value
     * @return string
     */
    public static function decode($value)
    {
        return html_entity_decode($value, ENT_QUOTES, Config::get('statamic.system.charset', 'UTF-8'));
    }

    /**
     * Convert an HTML string to entities.
     *
     * @param  string  $value
     * @return string
     */
    public static function entities($value)
    {
        return htmlentities($value, ENT_QUOTES, Config::get('statamic.system.charset', 'UTF-8'), false);
    }

    /**
     * Generate an ordered list of items.
     *
     * @param  array  $list
     * @param  array  $attributes
     * @return \Illuminate\Support\HtmlString|string
     */
    public static function ol($list, $attributes = [])
    {
        return static::listing('ol', $list, $attributes);
    }

    /**
     * Generate an un-ordered list of items.
     *
     * @param  array  $list
     * @param  array  $attributes
     * @return \Illuminate\Support\HtmlString|string
     */
    public static function ul($list, $attributes = [])
    {
        return static::listing('ul', $list, $attributes);
    }

    /**
     * Generate a description list of items.
     *
     * @param  array  $list
     * @param  array  $attributes
     * @return \Illuminate\Support\HtmlString
     */
    public static function dl(array $list, array $attributes = [])
    {
        $attributes = static::attributes($attributes);

        $html = "<dl{$attributes}>";

        foreach ($list as $key => $value) {
            $value = (array) $value;

            $html .= "<dt>$key</dt>";

            foreach ($value as $v_key => $v_value) {
                $html .= "<dd>$v_value</dd>";
            }
        }

        $html .= '</dl>';

        return static::toHtmlString($html);
    }

    /**
     * Create a listing HTML element.
     *
     * @param  string  $type
     * @param  array  $list
     * @param  array  $attributes
     * @return \Illuminate\Support\HtmlString|string
     */
    protected static function listing($type, $list, $attributes = [])
    {
        $html = '';

        if (count($list) === 0) {
            return $html;
        }

        // Essentially we will just spin through the list and build the list of the HTML
        // elements from the array. We will also handled nested lists in case that is
        // present in the array. Then we will build out the final listing elements.
        foreach ($list as $key => $value) {
            $html .= static::listingElement($key, $type, $value);
        }

        $attributes = static::attributes($attributes);

        return static::toHtmlString("<{$type}{$attributes}>{$html}</{$type}>");
    }

    /**
     * Create the HTML for a listing element.
     *
     * @param  mixed  $key
     * @param  string  $type
     * @param  mixed  $value
     * @return string
     */
    protected static function listingElement($key, $type, $value)
    {
        if (is_array($value)) {
            return static::nestedListing($key, $type, $value);
        } else {
            return '<li>'.e($value, false).'</li>';
        }
    }

    /**
     * Obfuscate a string to prevent spam-bots from sniffing it.
     *
     * @param  string  $value
     * @return string
     */
    public static function obfuscate($value)
    {
        $safe = '';

        foreach (str_split($value) as $letter) {
            if (ord($letter) > 128) {
                return $letter;
            }

            // To properly obfuscate the value, we will randomly convert each letter to
            // its entity or hexadecimal representation, keeping a bot from sniffing
            // the randomly obfuscated letters out of the string on the responses.
            switch (rand(1, 3)) {
                case 1:
                    $safe .= '&#'.ord($letter).';';
                    break;
                case 2:
                    $safe .= '&#x'.dechex(ord($letter)).';';
                    break;
                case 3:
                    $safe .= $letter;
            }
        }

        return $safe;
    }

    /**
     * Generate a link to a Favicon file.
     *
     * @param  string  $url
     * @return \Illuminate\Support\HtmlString
     */
    public static function favicon($url)
    {
        $attributes = static::attributes([
            'rel' => 'shortcut icon',
            'type' => 'image/x-icon',
            'href' => $url,
        ]);

        return static::toHtmlString('<link'.$attributes.'>');
    }

    /**
     * Generate a HTML link.
     *
     * @param  string  $url
     * @param  string  $title
     * @param  array  $attributes
     * @return \Illuminate\Support\HtmlString
     */
    public static function link($url, $title = null, $attributes = [])
    {
        if (is_null($title) || $title === false) {
            $title = $url;
        }

        $title = static::entities($title);

        return static::toHtmlString('<a href="'.static::entities($url).'"'.static::attributes($attributes).'>'.$title.'</a>');
    }

    /**
     * Parse each text part of an HTML string (no tags) through a callback function.
     *
     * @param  string  $value
     * @param  Closure  $callback
     * @return string
     */
    public static function mapText($value, Closure $callback)
    {
        return Str::mapRegex($value, '/(<[^>]+>)/', function ($part, $match) use ($callback) {
            return ! $match ? $callback($part) : $part;
        });
    }

    /**
     * Generate a HTML link to an email address.
     *
     * @param  string  $email
     * @param  string  $title
     * @param  array  $attributes
     * @param  bool  $escape
     * @return \Illuminate\Support\HtmlString
     */
    public static function mailto($email, $title = null, $attributes = [], $escape = true)
    {
        $email = static::email($email);

        $title = $title ?: $email;

        if ($escape) {
            $title = static::entities($title);
        }

        $email = static::obfuscate('mailto:').$email;

        return static::toHtmlString('<a href="'.$email.'"'.static::attributes($attributes).'>'.$title.'</a>');
    }

    /**
     * Obfuscate an e-mail address to prevent spam-bots from sniffing it.
     *
     * @param  string  $email
     * @return string
     */
    public static function email($email)
    {
        return str_replace('@', '&#64;', static::obfuscate($email));
    }

    public static function markdown($string)
    {
        return Markdown::parse($string);
    }

    public static function smartypants($string)
    {
        return SmartyPants::defaultTransform($string, SmartyPants::ATTR_DEFAULT);
    }

    /**
     * Sanitizes a string.
     *
     * @param  string|array  $value  The value to sanitize
     * @param  bool  $antlers  Whether Antlers (curly braces) should be escaped.
     * @return string
     */
    public static function sanitize($value, $antlers = true)
    {
        if (is_array($value)) {
            return Arr::sanitize($value, $antlers);
        }

        $value = self::entities($value);

        if ($antlers) {
            $value = str_replace(['{', '}'], ['&lbrace;', '&rbrace;'], $value);
        }

        return $value;
    }
}
