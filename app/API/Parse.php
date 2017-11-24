<?php

namespace Statamic\API;

use Statamic\View\Antlers\Template as Antlers;

/**
 * Parsing things. Templates, Markdown, YAML, etc.
 */
class Parse
{
    /**
     * Parse a string/template
     *
     * @param       $str        String to parse
     * @param array $variables  Variables to use
     * @param array $context    Contextual variables to also use
     * @return string
     */
    public static function template($str, $variables = [], $context = [])
    {
        return Antlers::parse($str, $variables, $context);
    }

    /**
     * Iterate over an array and parse the string/template for each
     *
     * @param string  $content     String to parse
     * @param array   $data        Variables to use, in a multidimensional array
     * @param bool    $supplement  Whether to supplement with contextual values
     * @param array   $context     Contextual variables to also use
     * @return string
     */
    public static function templateLoop($content, $data, $supplement = true, $context = [])
    {
        return Antlers::parseLoop($content, $data, $supplement, $context);
    }

    /**
     * Parse a string of raw YAML into an array
     *
     * @param string $str  The YAML string
     * @return array
     */
    public static function YAML($str)
    {
        return YAML::parse($str);
    }

    /**
     * Checks for and parses front matter
     *
     * @param string  $string  Content to parse
     * @return array
     */
    public static function frontMatter($string)
    {
        $data = [];
        $content = $string;

        if (Str::startsWith($string, "---".PHP_EOL)) {
            $data = self::YAML($string);
            $content = $data['content'];
            unset($data['content']);
        }

        return compact('data', 'content');
    }

    /**
     * Parse environment variable placeholders with the actual values
     *
     * @param   mixed  $val  The value to parse
     * @return  mixed
     */
    public static function env($val)
    {
        if (! Str::contains($val, '{env:')) {
            return $val;
        }

        // Value has an environment variable in it. Replace it.
        preg_match('/{env:(.*)\s?}/', $val, $matches);

        if (! isset($matches[0])) {
            // False alarm. They might just have `{env:` somewhere. Javascript?
            return $val;
        }

        return str_replace($matches[0], env($matches[1]), $val);
    }
}
