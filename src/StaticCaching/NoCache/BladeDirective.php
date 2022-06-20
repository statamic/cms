<?php

namespace Statamic\StaticCaching\NoCache;

class BladeDirective
{
    /**
     * @var NoCacheManager
     */
    private $noCacheManager;

    public function __construct(NoCacheManager $noCacheManager)
    {
        $this->noCacheManager = $noCacheManager;
    }

    public function handle($expression, $context)
    {
        $view = $expression;

        return $this
            ->noCacheManager->session()
            ->pushView($view, $context);
    }
}
