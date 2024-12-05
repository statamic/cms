<?php

namespace Statamic\View\Antlers\Language\Utilities;

use Exception;
use Illuminate\Support\Str;
use Statamic\View\Antlers\Language\Parser\DocumentParser;

class StringUtilities
{
    const SPLIT_METHOD_STR_SPLIT = 1;
    const SPLIT_METHOD_MB_STR_SPLIT = 2;
    public static $splitMethod = 1;

    public static function prepareSplit($text)
    {
        if (self::getMethod($text)) {
            StringUtilities::$splitMethod = StringUtilities::SPLIT_METHOD_MB_STR_SPLIT;
        } else {
            StringUtilities::$splitMethod = StringUtilities::SPLIT_METHOD_STR_SPLIT;
        }
    }

    protected static $methodCache = [];

    protected static function getMethod($text)
    {
        if (! array_key_exists($text, self::$methodCache)) {
            self::$methodCache[$text] = mb_strlen($text, 'utf-8') < strlen($text);
        }

        return self::$methodCache[$text];
    }

    public static function substr($string, $start = null, $length = null)
    {
        if (self::getMethod($string)) {
            return mb_substr($string, $start, $length);
        }

        if ($length === null) {
            return substr($string, $start);
        }

        return substr($string, $start, $length);
    }

    public static function split($text)
    {
        if (self::$splitMethod === self::SPLIT_METHOD_STR_SPLIT) {
            return str_split($text);
        }

        return mb_str_split($text);
    }

    public static function containsSymbolicCharacters($text)
    {
        $chars = str_split($text);

        foreach ($chars as $char) {
            if (ctype_punct($char) && $char != DocumentParser::LeftBracket &&
                $char != DocumentParser::RightBracket &&
                $char != DocumentParser::Punctuation_Colon &&
                $char != DocumentParser::Punctuation_Underscore &&
                $char != DocumentParser::Punctuation_FullStop) {
                return true;
            }
        }

        return false;
    }

    /**
     * Replaces PHP opening tags with their HTML-entity equivalents.
     *
     * @param  string  $text  The content to sanitize.
     * @return string
     */
    public static function sanitizePhp($text)
    {
        $text = str_replace('<?php', '&lt;?php', $text);

        // Also replace short tags if they're enabled.
        if (ini_get('short_open_tag')) {
            $xmlPlaceholder = '__XML_PLACEHOLDER'.Str::uuid();
            $text = str_replace('<?xml', $xmlPlaceholder, $text);
            $text = str_replace('<?', '&lt;?', $text);
            $text = str_replace($xmlPlaceholder, '<?xml', $text);
        }

        return $text;
    }

    /**
     * Attempts to determine the type of newline.
     *
     * @param  string  $string  The content to analyze.
     * @return string
     */
    public static function detectNewLineStyle($string)
    {
        $arr = array_count_values(
            explode(
                ' ',
                preg_replace(
                    '/[^\r\n]*(\r\n|\n|\r)/',
                    '\1 ',
                    $string
                )
            )
        );
        arsort($arr);

        $style = key($arr);

        if ($style == null) {
            $style = "\n";
        }

        return $style;
    }

    /**
     * Replaces all existing newline styles with the desired newline style.
     *
     * @param  string  $string  The content to update.
     * @param  string  $to  The desired newline style.
     * @return string
     */
    public static function normalizeLineEndings($string, $to = "\n")
    {
        return preg_replace("/\r\n|\r|\n/", $to, $string);
    }

    /**
     * Converts a string into an array of strings, by new line character.
     *
     * @param  string  $input  The content to analyze.
     * @return false|string[]
     */
    public static function breakByNewLine($input)
    {
        return explode("\n", $input);
    }

    /**
     * Indents the string with the desired number of spaces.
     *
     * @param  string  $string  The string to indent.
     * @param  number  $indent  The number of spaces.
     * @return string
     */
    public static function indentString($string, $indent)
    {
        $strIndent = str_repeat(' ', $indent);
        $lines = self::breakByNewLine(self::normalizeLineEndings($string));
        $indented = [];

        foreach ($lines as $line) {
            $indented[] = $strIndent.$line;
        }

        return implode("\n", $indented);
    }

    /**
     * Generates a new UUID-v4 compatible string.
     *
     * @param  null  $data  Random byte data.
     * @return string
     *
     * @throws Exception
     */
    public static function uuidv4($data = null)
    {
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0F | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3F | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
