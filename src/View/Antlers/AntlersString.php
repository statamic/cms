<?php

namespace Statamic\View\Antlers;

use Statamic\Contracts\View\Antlers\Parser;

class AntlersString
{
    protected $string;
    protected $parser;
    protected $injectExtractions = true;

    public function __construct(string $string, Parser $parser)
    {
        $this->string = $string;
        $this->parser = $parser;
    }

    public function withoutExtractions()
    {
        $this->injectExtractions = false;

        return $this;
    }

    public function __toString()
    {
        return $this->injectExtractions
            ? $this->parser->injectNoparse($this->string)
            : $this->string;
    }
}
