<?php

namespace Statamic\API;

/**
 * Miscellaneous helper methods
 */
class Helper
{
    /**
     * Reverse string position count
     *
     * Get the nth-last position of a substring
     *
     * @param     $haystack
     * @param     $needle
     * @param int $instance  The number of times to go back
     * @return bool|int
     */
    public static function strrposCount($haystack, $needle, $instance = 0)
    {
        do {
            // get the last occurrence in the current haystack
            $last = strrpos($haystack, $needle);

            if ($last === false) {
                return false;
            }

            $haystack = substr($haystack, 0, $last);
            $instance--;
        } while ($instance >= 0);

        return $last;
    }

    /**
     * Grabs the first value from an array. If a string is provided it will return it.
     *
     * @param mixed $value
     * @return mixed
     */
    public static function grab($value)
    {
        $arr = self::ensureArray($value);

        return reset($arr);
    }

    /**
     * Ensures that a given variable is an array
     *
     * @param mixed $value  Variable to check
     * @return array
     **/
    public static function ensureArray($value)
    {
        if (! is_array($value)) {
            return array($value);
        }

        return $value;
    }

    /**
     * Ensure that a given value is one of the options. Otherwise return the first option.
     *
     * @param mixed $value  The value being checked.
     * @param array $options  An array of options the value is allowed to be.
     * @return mixed
     */
    public static function ensureInArray($value, array $options)
    {
        if (! in_array($value, $options)) {
            return reset($options);
        }

        return $value;
    }

    /**
     * Checks whether the given $value is an empty array or not
     *
     * @param mixed  $value  Value to check
     * @return bool
     */
    public static function isEmptyArray($value)
    {
        if (is_array($value)) {
            foreach ($value as $subvalue) {
                if (!self::isEmptyArray($subvalue)) {
                    return FALSE;
                }
            }
        } elseif (!empty($value) || $value !== '') {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Creates a sentence list from the given $list
     *
     * @param array  $list  List of items to list
     * @param string  $glue  Joining string before the last item when more than one item
     * @param bool  $oxford_comma  Include a comma before $glue?
     * @return string
     */
    public static function makeSentenceList(Array $list, $glue = "and", $oxford_comma = true)
    {
        $length = count($list);

        switch ($length) {
            case 0:
            case 1:
                return join("", $list);
                break;

            case 2:
                return join(" " . $glue . " ", $list);
                break;

            default:
                $last = array_pop($list);
                $sentence  = join(", ", $list);
                $sentence .= ($oxford_comma) ? "," : "";

                return $sentence . " " . $glue . " " . $last;
        }
    }

    /**
     * Explodes options into an array
     *
     * @param string  $string  String to explode
     * @param bool $keyed  Are options keyed?
     * @return array
     */
    public static function explodeOptions($string, $keyed=FALSE)
    {
        $options = explode('|', $string);

        if ($keyed) {

            $temp_options = array();
            foreach ($options as $value) {

                if (strpos($value, ':')) {
                    # key:value pair present
                    list($option_key, $option_value) = explode(':', $value);
                } else {
                    # default value is false
                    $option_key   = $value;
                    $option_value = FALSE;
                }

                # set the main options array
                $temp_options[$option_key] = $option_value;
            }
            # reassign and override
            $options = $temp_options;
        }

        return $options;
    }

    /**
     * Normalize arguments
     *
     * Ensures both ['one', 'two'] or 'one|two' ends up as the former
     *
     * @param mixed
     * @return array
     */
    public static function normalizeArguments($args)
    {
        $output = [];

        foreach ($args as $arg) {
            if (! is_array($arg)) {
                $arg = explode('|', $arg);
            }

            $output = array_merge($output, $arg);
        }

        return array_unique($output);
    }

    /**
     * Picks the first value that isn't null
     *
     * @return mixed
     */
    public static function pick()
    {
        $args = func_get_args();

        if (!is_array($args) || !count($args)) {
            return null;
        }

        foreach ($args as $arg) {
            if (!is_null($arg)) {
                return $arg;
            }
        }

        return null;
    }

    public static function stripTags($html, $tags_list = [])
    {

        if (count($tags_list) > 0) {


            $all_tags = [
                "a", "abbr", "acronym", "address", "applet",
                "area", "article", "aside", "audio", "b",
                "base", "basefont", "bdi", "bdo", "big",
                "blockquote", "body", "br", "button", "canvas",
                "caption", "center", "cite", "code", "col",
                "colgroup", "command", "data", "datagrid", "datalist",
                "dd", "del", "details", "dfn", "dir", "div", "dl",
                "dt", "em", "embed", "eventsource", "fieldset",
                "figcaption", "figure", "font", "footer", "form",
                "frame", "frameset", "h1", "h2", "h3", "h4", "h5",
                "h6", "head", "header", "hgroup", "hr", "html", "i",
                "iframe", "img", "input", "isindex", "ins", "kbd",
                "keygen", "label", "legend", "li", "link", "main",
                "mark", "map", "menu", "meta", "meter", "nav",
                "noframes", "noscript", "object", "ol", "optgroup",
                "option", "output", "p", "param", "pre", "progress",
                "q", "ruby", "rp", "rt", "s", "samp", "script",
                "section", "select", "small", "source", "span",
                "strike", "strong", "style", "sub", "summary", "sup",
                "table", "tbody", "td", "textarea", "tfoot", "th",
                "thead", "time", "title", "tr", "track", "tt", "u",
                "ul", "var", "video", "wbr"
            ];

            $allowed_tags = array_diff($all_tags, $tags_list);
            $allowed_tag_string = "<" . join("><", $allowed_tags) . ">";

            return strip_tags($html, $allowed_tag_string);
        }

        return strip_tags($html);
    }

    /**
     * Attempts to prevent widows in a string by adding a
     * &nbsp; between the last two words of each paragraph.
     *
     * @param string $value
     * @return string
     */
    public static function widont($value)
    {
        // thanks to Shaun Inman for inspiration here
        // http://www.shauninman.com/archive/2008/08/25/widont_2_1_1

        // if there are content tags
        if (preg_match("/<\/(?:p|li|h1|h2|h3|h4|h5|h6|figcaption)>/ism", $value)) {
            // step 1, replace spaces in HTML tags with a code
            $value = preg_replace_callback("/<.*?>/ism", function($matches) {
                return str_replace(' ', '%###%##%', $matches[0]);
            }, $value);

            // step 2, replace last space with &nbsp;
            $value = preg_replace("/(?<!<[p|li|h1|h2|h3|h4|h5|h6|div|figcaption])([^\s])[ \t]+([^\s]+(?:[\s]*<\/(?:p|li|h1|h2|h3|h4|h5|h6|div|figcaption)>))$/im", "$1&nbsp;$2", rtrim($value));

            // step 3, re-replace the code from step 1 with spaces
            return str_replace("%###%##%", " ", $value);

            // otherwise
        } else {
            return preg_replace("/([^\s])\s+([^\s]+)\s*$/im", "$1&nbsp;$2", rtrim($value));
        }
    }

    /**
     * Compares two values
     *
     * Returns 1 if first is greater, -1 if second is, 0 if same
     *
     * @param mixed $one Value 1 to compare
     * @param mixed $two Value 2 to compare
     * @return int
     */
    public static function compareValues($one, $two)
    {
        // something is null
        if (is_null($one) || is_null($two)) {
            if (is_null($one) && !is_null($two)) {
                return 1;
            } elseif (!is_null($one) && is_null($two)) {
                return -1;
            }

            return 0;
        }

        // something is an array
        if (is_array($one) || is_array($two)) {
            if (is_array($one) && !is_array($two)) {
                return 1;
            } elseif (!is_array($one) && is_array($two)) {
                return -1;
            }

            return 0;
        }

        // something is an object
        if (is_object($one) || is_object($two)) {
            if (is_object($one) && !is_object($two)) {
                return 1;
            } elseif (!is_object($one) && is_object($two)) {
                return -1;
            }

            return 0;
        }

        // something is a boolean
        if (is_bool($one) || is_bool($two)) {
            if ($one && !$two) {
                return 1;
            } elseif (!$one && $two) {
                return -1;
            }

            return 0;
        }

        // string based
        if (!is_numeric($one) || !is_numeric($two)) {
            return Str::compare($one, $two, null, false);
        }

        // number-based
        if ($one > $two) {
            return 1;
        } elseif ($one < $two) {
            return -1;
        }

        return 0;
    }

    /**
     * Make a UUID
     *
     * @return string
     */
    public static function makeUuid()
    {
        return (string) \Uuid::generate(4);
    }
}
