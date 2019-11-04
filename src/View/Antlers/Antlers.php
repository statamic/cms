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
    public function parseLoop($content, $data, $supplement = true)
    {
        $total  = count($data);
        $i = 0;

        return collect($data)->reduce(function ($carry, $item) use ($content, &$i, $total, $supplement) {
            $i++;

            if ($supplement) {
                $item = array_merge($item, [
                    'first' => ($i === 1),
                    'last' => ($i === $total),
                    'zero_index' => $i - 1,
                    'index' => $i,
                    'count' => $i,
                    'total_results' => $total,
                ]);
            }

            return $carry . $this->parse($content, $item);
        }, '');
    }
}
