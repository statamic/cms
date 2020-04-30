<?php

namespace Statamic\Markdown;

use Closure;
use InvalidArgumentException;
use UnexpectedValueException;

class Manager
{
    protected $parsers = [];

    public function __call($method, $args)
    {
        return $this->parser('default')->$method(...$args);
    }

    public function makeParser(array $config = []): Parser
    {
        return new Parser($config);
    }

    public function parser(string $name)
    {
        if ($name === 'default' && ! $this->hasParser('default')) {
            return $this->parsers['default'] = $this->makeParser();
        }

        if (! $this->hasParser($name)) {
            throw new InvalidArgumentException("Markdown parser [$name] is not defined.");
        }

        return $this->parsers[$name];
    }

    public function hasParser(string $name): bool
    {
        return isset($this->parsers[$name]);
    }

    public function extend(string $name, Closure $closure)
    {
        $parser = $closure($this->makeParser());

        if (! $parser instanceof Parser) {
            throw new UnexpectedValueException('A '.Parser::class.' instance is expected.');
        }

        $this->parsers[$name] = $parser;
    }
}
