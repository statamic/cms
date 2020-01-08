<?php

namespace Statamic\Markdown;

use InvalidArgumentException;
use Statamic\Markdown\Parser;

class Manager
{
    protected $parsers = [];

    public function __call($method, $args)
    {
        return $this->defaultParser()->$method(...$args);
    }

    public function makeParser(array $config = []): Parser
    {
        return new Parser($config);
    }

    public function defaultParser()
    {
        if (! $this->hasParser('default')) {
            $this->setParser('default', $this->makeParser());
        }

        return $this->parser('default');
    }

    public function parser(string $name)
    {
        if (! $this->hasParser($name)) {
            throw new InvalidArgumentException("Markdown parser [$name] is not defined.");
        }

        return $this->parsers[$name];
    }

    public function setParser(string $name, Parser $parser = null)
    {
        $this->parsers[$name] = $parser;
    }

    public function hasParser(string $name): bool
    {
        return isset($this->parsers[$name]);
    }
}
