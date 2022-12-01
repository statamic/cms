<?php

namespace Statamic\Support;

use Closure;
use Statamic\Facades\Compare;
use Stringy\StaticStringy;
use voku\helper\ASCII;

/**
 * Manipulating strings.
 */
class Str extends \Illuminate\Support\Str
{
    public static function __callStatic($method, $parameters)
    {
        return call_user_func_array([StaticStringy::class, $method], $parameters);
    }

    public static function ascii($value, $language = 'en')
    {
        return ASCII::to_ascii((string) $value, $language, true, config('statamic.system.ascii_replace_extra_symbols'));
    }

    /**
     * Creates a sentence list from the given $list.
     *
     * @param  array  $list  List of items to list
     * @param  string  $glue  Joining string before the last item when more than one item
     * @param  bool  $oxford_comma  Include a comma before $glue?
     * @return string
     */
    public static function makeSentenceList(array $list, $glue = 'and', $oxford_comma = true)
    {
        $length = count($list);

        switch ($length) {
            case 0:
            case 1:
                return implode('', $list);
                break;

            case 2:
                return implode(' '.$glue.' ', $list);
                break;

            default:
                $last = array_pop($list);
                $sentence = implode(', ', $list);
                $sentence .= ($oxford_comma) ? ',' : '';

                return $sentence.' '.$glue.' '.$last;
        }
    }

    public static function stripTags($html, $tags_list = [])
    {
        if (count($tags_list) > 0) {
            $all_tags = [
                'a', 'abbr', 'acronym', 'address', 'applet',
                'area', 'article', 'aside', 'audio', 'b',
                'base', 'basefont', 'bdi', 'bdo', 'big',
                'blockquote', 'body', 'br', 'button', 'canvas',
                'caption', 'center', 'cite', 'code', 'col',
                'colgroup', 'command', 'data', 'datagrid', 'datalist',
                'dd', 'del', 'details', 'dfn', 'dir', 'div', 'dl',
                'dt', 'em', 'embed', 'eventsource', 'fieldset',
                'figcaption', 'figure', 'font', 'footer', 'form',
                'frame', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5',
                'h6', 'head', 'header', 'hgroup', 'hr', 'html', 'i',
                'iframe', 'img', 'input', 'isindex', 'ins', 'kbd',
                'keygen', 'label', 'legend', 'li', 'link', 'main',
                'mark', 'map', 'menu', 'meta', 'meter', 'nav',
                'noframes', 'noscript', 'object', 'ol', 'optgroup',
                'option', 'output', 'p', 'param', 'pre', 'progress',
                'q', 'ruby', 'rp', 'rt', 's', 'samp', 'script',
                'section', 'select', 'small', 'source', 'span',
                'strike', 'strong', 'style', 'sub', 'summary', 'sup',
                'table', 'tbody', 'td', 'textarea', 'tfoot', 'th',
                'thead', 'time', 'title', 'tr', 'track', 'tt', 'u',
                'ul', 'var', 'video', 'wbr',
            ];

            $allowed_tags = array_diff($all_tags, $tags_list);
            $allowed_tag_string = '<'.implode('><', $allowed_tags).'>';

            return strip_tags($html, $allowed_tag_string);
        }

        return strip_tags($html);
    }

    public static function slug($string, $separator = '-', $language = 'en', $dictionary = ['@' => 'at'])
    {
        $string = (string) $string;

        // Statamic is a-OK with underscores in slugs.
        $string = str_replace('_', $placeholder = strtolower(str_random(16)), $string);

        $slug = parent::slug($string, $separator, $language, $dictionary);

        return str_replace($placeholder, '_', $slug);
    }

    public static function studlyToSlug($string)
    {
        return parent::slug(Str::snake($string));
    }

    public static function studlyToTitle($string)
    {
        return Str::modifyMultiple($string, ['snake', 'slugToTitle']);
    }

    public static function studlyToWords($string)
    {
        return Str::modifyMultiple($string, ['snake', 'deslugify']);
    }

    public static function slugToTitle($string)
    {
        return Str::modifyMultiple($string, ['deslugify', 'title']);
    }

    public static function isUrl($string)
    {
        return self::startsWith($string, '/') || filter_var($string, FILTER_VALIDATE_URL) !== false;
    }

    public static function deslugify($string)
    {
        return str_replace(['-', '_'], ' ', $string);
    }

    /**
     * Get the human file size of a given file.
     *
     * @param  int  $bytes
     * @param  int  $decimals
     * @return string
     */
    public static function fileSizeForHumans($bytes, $decimals = 2)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, $decimals).' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, $decimals).' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, $decimals).' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes.' B';
        } elseif ($bytes == 1) {
            $bytes = $bytes.' B';
        } else {
            $bytes = '0 B';
        }

        return $bytes;
    }

    public static function timeForHumans($ms)
    {
        if ($ms < 1000) {
            return $ms.'ms';
        }

        return round($ms / 1000, 2).'s';
    }

    /**
     * Attempts to prevent widows in a string by adding a
     * &nbsp; between the last two words of each paragraph.
     *
     * @param  string  $value
     * @return string
     */
    public static function widont($value, $words = 1)
    {
        // thanks to Shaun Inman for inspiration here
        // http://www.shauninman.com/archive/2008/08/25/widont_2_1_1
        // if there are content tags
        if (preg_match("/<\/(?:p|li|h1|h2|h3|h4|h5|h6|figcaption)>/ism", $value)) {
            // step 1, replace spaces in HTML tags with a code
            $value = preg_replace_callback('/<.*?>/ism', function ($matches) {
                return str_replace(' ', '%###%##%', $matches[0]);
            }, $value);

            // step 2, replace all tabs and spaces based on params with &nbsp;
            $value = preg_replace_callback("/(?<!<[p|li|h1|h2|h3|h4|h5|h6|div|figcaption])([^\s]\s)([^\s]*\s?){{$words}}(<\/(?:p|li|h1|h2|h3|h4|h5|h6|div|figcaption)>)/", function ($matches) {
                return preg_replace('/([[:blank:]])/', '&nbsp;', rtrim($matches[0]));
            }, $value);

            // Step 3, handle potential nested list orphans
            $value = preg_replace_callback("/(?<!<[li])([^\s]\s)([^\s]*\s?){{$words}}(<(?:ol|ul)>)/", function ($matches) {
                return preg_replace('/[[:blank:]]/', '&nbsp;', rtrim($matches[0]));
            }, $value);

            // step 4, re-replace the code from step 1 with spaces
            return str_replace('%###%##%', ' ', $value);

        // otherwise
        } else {
            return preg_replace_callback("/([^\s]\s)([^\s]*\s?){{$words}}$/im", function ($matches) {
                return preg_replace("/([\s])/", '&nbsp;', rtrim($matches[0]));
            }, $value);
        }
    }

    /**
     * Compare two strings.
     *
     * @param  string  $str1  First string to compare.
     * @param  string  $str2  Second string to compare.
     */
    public static function compare($str1, $str2)
    {
        return Compare::strings($str1, $str2);
    }

    /**
     * Parse each part of a string split with a regex through a callback function.
     *
     * @param  string  $value
     * @param  string  $regex
     * @param  Closure  $callback
     * @return string
     */
    public static function mapRegex($value, $regex, Closure $callback)
    {
        $parts = preg_split($regex, $value, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        foreach ($parts as $i => $part) {
            $parts[$i] = $callback($part, preg_match($regex, $part));
        }

        return implode('', $parts);
    }

    /**
     * Apply multiple string modifications via array.
     *
     * @param  string  $string
     * @param  array  $modifications
     * @return string
     */
    public static function modifyMultiple($string, $modifications)
    {
        foreach ($modifications as $modification) {
            $string = is_callable($modification)
                ? $modification($string)
                : self::$modification($string);
        }

        return $string;
    }

    public static function tailwindWidthClass($width)
    {
        $widths = [
            25 => '1/4',
            33 => '1/3',
            50 => '1/2',
            66 => '2/3',
            75 => '3/4',
            100 => 'full',
        ];

        $class = $widths[$width] ?? 'full';

        return "w-$class";
    }

    /**
     * Output either literal "true" or "false" strings given a boolean.
     *
     * @param  bool  $value
     * @return string
     */
    public static function bool(bool $value): string
    {
        return ((bool) $value) ? 'true' : 'false';
    }

    /**
     * Get an actual boolean from a string based boolean.
     *
     * @param  mixed  $value
     * @return bool
     */
    public static function toBool($value): bool
    {
        return ! in_array(strtolower($value), ['no', 'false', '0', '', '-1']);
    }

    public static function replace($string, $search, $replace)
    {
        return StaticStringy::replace($string, $search, $replace);
    }

    public static function safeTruncateReverse($string, $length, $substring = '')
    {
        return self::reverse(self::safeTruncate(self::reverse($string), $length, $substring));
    }

    public static function studly($value)
    {
        $key = $value;

        if (isset(parent::$studlyCache[$key])) {
            return parent::$studlyCache[$key];
        }

        $words = explode(' ', str_replace(['-', '_'], ' ', $value));

        $studlyWords = array_map(function ($word) {
            return parent::ucfirst($word);
        }, $words);

        return parent::$studlyCache[$key] = implode($studlyWords);
    }
}
