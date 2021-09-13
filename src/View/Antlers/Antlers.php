<?php

namespace Statamic\View\Antlers;

use Closure;
use Statamic\Contracts\Antlers\ParserContract;

class Antlers
{
    protected $parser;

    public function parser()
    {
        return $this->parser ?? app(ParserContract::class);
    }

    public function usingParser(ParserContract $parser, Closure $callback)
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
     * @param  string  $content
     * @param  array  $data
     * @param  bool  $supplement
     * @param  array  $context
     * @return string
     */
    public function parseLoop($content, $data, $supplement = true, $context = [])
    {
        return new AntlersLoop($this->parser(), $content, $data, $supplement, $context);
    }
}
