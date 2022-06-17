<?php

namespace Statamic\StaticCaching\NoCache;

class Tags extends \Statamic\Tags\Tags
{
    public static $handle = 'no_cache';
    public static $stack = 0;

    /**
     * @var NoCacheManager
     */
    private $noCacheManager;

    public function __construct(NoCacheManager $noCacheManager)
    {
        $this->noCacheManager = $noCacheManager;
    }

    public function index()
    {
        static::$stack += 1;

        if (static::$stack <= 1) {
            static::$stack -= 1;

            return $this
                ->noCacheManager->session()
                ->pushSection($this->content, $this->context->all(), 'antlers.html');
        }

        return [];
    }
}
