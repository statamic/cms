<?php

namespace Statamic\Facades\Endpoint;

use Statamic\Facades\Antlers;
use Statamic\Facades\YAML;
use Statamic\Support\Str;

/**
 * Parsing things. Templates, Markdown, YAML, etc.
 */
class Parse
{
    /**
     * Parse a string/template.
     *
     * @param  string  $str  String to parse
     * @param  array  $variables  Variables to use
     * @param  array  $context  Contextual variables to also use
     * @param  bool  $php  Whether PHP should be allowed
     * @return string
     */
    public function template($str, $variables = [], $context = [], $php = false)
    {
        return Antlers::parse($str, $variables, $context, $php);
    }

    /**
     * Iterate over an array and parse the string/template for each.
     *
     * @param  string  $content  String to parse
     * @param  array  $data  Variables to use, in a multidimensional array
     * @param  bool  $supplement  Whether to supplement with contextual values
     * @param  array  $context  Contextual variables to also use
     * @param  bool  $php  Whether PHP should be allowed
     * @return string
     */
    public function templateLoop($content, $data, $supplement = true, $context = [], $php = false)
    {
        return Antlers::parseLoop($content, $data, $supplement, $context, $php);
    }

    /**
     * Parse a string of raw YAML into an array.
     *
     * @param  string  $str  The YAML string
     * @return array
     */
    public function YAML($str)
    {
        return YAML::parse($str);
    }

    /**
     * Checks for and parses front matter.
     *
     * @param  string  $string  Content to parse
     * @return array
     */
    public function frontMatter($string)
    {
        $data = [];
        $content = $string;

        if (preg_match('/^---[\r\n?|\n]/', $string)) {
            $data = self::YAML($string);
            $content = $data['content'];
            unset($data['content']);
        }

        return compact('data', 'content');
    }

    /**
     * Parse environment variable placeholders with the actual values.
     *
     * @param  mixed  $val  The value to parse
     * @return mixed
     */
    public function env($val)
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
