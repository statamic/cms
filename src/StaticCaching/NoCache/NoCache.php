<?php

namespace Statamic\StaticCaching\NoCache;

use Statamic\Tags\Context;
use Statamic\Tags\Tags;

class NoCache extends Tags
{
    protected static $handle = 'no_cache';
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
        if (NoCacheManager::$isRehydrated) {
            return $this->parse();
        }

        self::$stack += 1;

        if (self::$stack > 1) {
            $cacheManagerResult = (string) $this->parse();
        } else {
            $cacheManagerResult = $this->noCacheManager->session()
                ->pushSection($this->content, $this->context->all(), (string) $this->parse());
        }

        self::$stack -= 1;

        return $cacheManagerResult;
    }

    public function evaluate()
    {
        $region = $this->params->get('region', null);

        if ($region != null) {
            $region = '__no_cache_section_'.$region;

            if ($this->noCacheManager->session()->hasSection($region)) {
                $this->context = new Context($this->noCacheManager->session()->getSectionData($region));
                $this->content = $this->noCacheManager->session()->getSectionContent($region);

                return $this->parse();
            }
        }

        return '';
    }
}