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
     * Iterate over an array and parse the string/template for each.
     *
     * @param string  $content
     * @param array   $data
     * @param bool    $supplement
     * @param array   $context
     * @return string
     */
    public function parseLoop($content, $data, $supplement = true, $context = [])
    {
        $total = count($data);
        $i = 0;

        return collect($data)->reduce(function ($carry, $item) use ($content, &$i, $total, $supplement, $context) {
            if ($supplement) {
                $item = array_merge($item, [
                    'index' => $i,
                    'count' => $i + 1,
                    'total_results' => $total,
                    'first' => ($i === 0),
                    'last' => ($i === $total - 1),
                ]);
            }

            $i++;

            return $carry.$this->parse($content, array_merge($context, $item));
        }, '');
    }
}
