<?php

namespace Statamic\StaticCaching\NoCache;

class Tags extends \Statamic\Tags\Tags
{
    public static $handle = 'nocache';
    public static $stack = 0;

    /**
     * @var CacheSession
     */
    private $nocache;

    public function __construct(CacheSession $nocache)
    {
        $this->nocache = $nocache;
    }

    public function index()
    {
        $key = $this
            ->nocache
            ->pushSection($this->content, $this->context->all(), 'antlers.html');

        return sprintf('<span class="nocache" data-nocache="%s"></span>', $key);
    }
}
