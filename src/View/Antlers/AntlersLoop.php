<?php

namespace Statamic\View\Antlers;

class AntlersLoop extends AntlersString
{
    protected $parser;
    protected $string;
    protected $variables;
    protected $supplement;
    protected $context;

    public function __construct($parser, $string, $variables, $supplement, $context)
    {
        $this->parser = $parser;
        $this->string = $string;
        $this->variables = $variables;
        $this->supplement = $supplement;
        $this->context = $context;
    }

    public function __toString()
    {
        $total = count($this->variables);
        $i = 0;

        $contents = collect($this->variables)->reduce(function ($carry, $item) use (&$i, $total) {
            if ($this->supplement) {
                $item = array_merge($item, [
                    'index' => $i,
                    'count' => $i + 1,
                    'total_results' => $total,
                    'first' => ($i === 0),
                    'last' => ($i === $total - 1),
                ]);
            }

            $i++;

            $parsed = $this->parser
                ->parse($this->string, array_merge($this->context, $item))
                ->withoutExtractions();

            return $carry.$parsed;
        }, '');

        $string = new AntlersString($contents, $this->parser);

        return (string) ($this->injectExtractions ? $string : $string->withoutExtractions());
    }
}
