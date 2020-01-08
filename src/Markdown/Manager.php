<?php

namespace Statamic\Markdown;

use Statamic\Markdown\Parser;

class Manager
{
    protected $defaultParser;

    public function __construct($defaultParser)
    {
        $this->defaultParser = $defaultParser;
    }

    public function __call($method, $args)
    {
        return $this->defaultParser->$method(...$args);
    }

    public function makeParser(array $config = []): Parser
    {
        return new Parser($config);
    }
}
