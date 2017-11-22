<?php

namespace Statamic\View\Antlers;

use Statamic\API\Config;

/**
 * Interaction layer for the template parser
 */
class Template
{
    /**
     * Parse a string/template
     *
     * @param       $str        String to parse
     * @param array $variables  Variables to use
     * @param array $context    Contextual variables to also use
     * @return string
     */
    public static function parse($str, $variables = [], $context = [])
    {
        $parser = new Parser;

        $parser->cumulativeNoparse(true);

        if (! is_array($variables)) {
            $variables = $variables->toArray();
        }

        $data = array_merge($context, $variables);

        return $parser->parse($str, $data, ['Statamic\View\Antlers\Engine', 'renderTag'], Config::get('theming.allow_php'));
    }

    /**
     * Iterate over an array and parse the string/template for each
     *
     * @param string  $content
     * @param array   $data
     * @param bool    $supplement
     * @param array   $context
     * @return string
     */
    public static function parseLoop($content, $data, $supplement, $context)
    {
        $output = '';
        $i      = 1;
        $total  = count($data);

        foreach ($data as $item) {
            if ($supplement) {
                $item['first']         = ($i === 1);
                $item['last']          = ($i === $total);
                $item['zero_index']    = $i - 1;
                $item['index']         = $i;
                $item['count']         = $i;
                $item['total_results'] = $total;
            }

            $output .= self::parse($content, $item, $context);
            $i++;
        }

        return $output;
    }
}
