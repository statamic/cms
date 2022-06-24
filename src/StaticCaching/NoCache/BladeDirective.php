<?php

namespace Statamic\StaticCaching\NoCache;

class BladeDirective
{
    /**
     * @var CacheSession
     */
    private $nocache;

    public function __construct(CacheSession $nocache)
    {
        $this->nocache = $nocache;
    }

    public function handle($expression, $context)
    {
        $view = $expression;

        $key = $this->nocache->pushView($view, $context);

        return sprintf('<span class="nocache" data-nocache="%s">NOCACHE_PLACEHOLDER</span>', $key);
    }
}
