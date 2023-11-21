<?php

namespace Statamic\View\Antlers;

use Closure;
use Statamic\Contracts\View\Antlers\Parser;
use Statamic\View\Antlers\Language\Parser\IdentifierFinder;

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

    public function identifiers(string $content): array
    {
        if (config('statamic.antlers.version') !== 'runtime') {
            throw new \Exception('Antlers identifiers can only be retrieved when using the runtime parser.');
        }

        return (new IdentifierFinder)->getIdentifiers($content);
    }
}
