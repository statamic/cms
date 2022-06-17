<?php

namespace Statamic\StaticCaching\NoCache;

class NoCacheManager
{
    /**
     * The active CacheSession instance.
     *
     * @var CacheSession|null
     */
    private $session = null;

    /**
     * Returns access to the current cache session.
     *
     * @return CacheSession
     */
    public function session()
    {
        if ($this->session == null) {
            $this->session = new CacheSession(request()->getUri());
        }

        return $this->session;
    }
}
