<?php

namespace Statamic\StaticCaching;

use Statamic\Support\Str;

class DefaultUrlExcluder implements UrlExcluder
{
    protected $cacher;
    protected $exclusions;

    public function __construct(Cacher $cacher, array $exclusions)
    {
        $this->cacher = $cacher;
        $this->exclusions = $exclusions;
    }

    public function getExclusions(): array
    {
        return $this->exclusions;
    }

    public function isExcluded(string $url): bool
    {
        // Query strings should be ignored.
        $url = explode('?', $url)[0];

        $url = Str::removeLeft($url, $this->cacher->getBaseUrl());

        foreach ($this->exclusions as $excluded) {
            if (Str::endsWith($excluded, '*') && Str::startsWith($url, substr($excluded, 0, -1))) {
                return true;
            }

            if ($url === $excluded) {
                return true;
            }
        }

        return false;
    }
}
