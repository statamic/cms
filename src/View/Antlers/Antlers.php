<?php

namespace Statamic\View\Antlers;

use Closure;
use Statamic\Contracts\View\Antlers\Parser;
use Statamic\View\Antlers\Language\Parser\IdentifierFinder;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Instrumentation\InstrumentationState;

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

    public function parse($str, $variables = [], $trusted = false)
    {
        $previousState = GlobalRuntimeState::$isEvaluatingUserData;
        GlobalRuntimeState::$isEvaluatingUserData = ! $trusted;

        try {
            return $this->parser()->parse($str, $variables);
        } finally {
            GlobalRuntimeState::$isEvaluatingUserData = $previousState;
        }
    }

    /** @internal */
    public function parseWithoutInstrumentation($str, $variables = [], $trusted = false)
    {
        return InstrumentationState::withoutInstrumentation(
            fn () => $this->parse($str, $variables, $trusted)
        );
    }

    /**
     * Iterate over an array and parse the string/template for each.
     *
     * @param  string  $content
     * @param  array  $data
     * @param  bool  $supplement
     * @param  array  $context
     * @param  bool  $trusted
     * @return string
     */
    public function parseLoop($content, $data, $supplement = true, $context = [], $trusted = false)
    {
        return new AntlersLoop($this->parser(), $content, $data, $supplement, $context, $trusted);
    }

    public function identifiers(string $content): array
    {
        return (new IdentifierFinder)->getIdentifiers($content);
    }
}
