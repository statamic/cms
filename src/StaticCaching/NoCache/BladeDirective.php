<?php

namespace Statamic\StaticCaching\NoCache;

class BladeDirective
{
    /**
     * @var Session
     */
    private $nocache;

    public function __construct(Session $nocache)
    {
        $this->nocache = $nocache;
    }

    public function handle($expression, $context)
    {
        $view = $expression;

        return $this->nocache->pushView($view, $context)->placeholder();
    }
}
