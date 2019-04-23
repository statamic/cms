<?php

namespace Statamic\View\Antlers;

use Closure;

class Antlers
{
    protected $parser;

    public function parser()
    {
        return $this->parser ?? app(Parser::class);
    }

    public function usingParser(Parser $parser, Closure $callback)
    {
        $this->parser = $parser;

        $contents = $callback($this);

        $this->parser = null;

        return $contents;
    }

    public function parse($str, $variables = [])
    {
        return $this->parser()->parse($str, $variables);
    }

    /**
     * Iterate over an array and parse the string/template for each
     *
     * @param string  $content
     * @param array   $data
     * @param bool    $supplement
     * @return string
     */
    public function parseLoop($content, $data, $supplement)
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

            $output .= $this->parse($content, $item);
            $i++;
        }

        return $output;
    }
}
