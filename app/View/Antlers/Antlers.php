<?php

namespace Statamic\View\Antlers;

class Antlers
{
    public function parse($str, $variables = [], $context = [], $php = false)
    {
        $parser = new Parser;

        $parser->cumulativeNoparse(true);

        if (! is_array($variables)) {
            $variables = $variables->toArray();
        }

        $data = array_merge($context, $variables);

        return $parser->parse($str, $data, ['Statamic\View\Antlers\Engine', 'renderTag'], $php);
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
    public function parseLoop($content, $data, $supplement, $context, $php = false)
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

            $output .= $this->parse($content, $item, $context, $php);
            $i++;
        }

        return $output;
    }
}
