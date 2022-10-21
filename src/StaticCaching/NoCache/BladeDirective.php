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

    public function handle($expression, array $params, array $data = null)
    {
        if (! isset($data)) {
            $data = $params;
            $params = [];
        }

        $view = $expression;

        $context = array_merge($data, $params);

        return $this->nocache->pushView($view, $context)->placeholder();
    }
}
