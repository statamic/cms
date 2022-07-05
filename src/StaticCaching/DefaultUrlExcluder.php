<?php

namespace Statamic\StaticCaching;

use Statamic\Support\Str;

class DefaultUrlExcluder implements UrlExcluder
{
    protected $baseUrl;
    protected $exclusions;

    public function __construct(string $baseUrl, array $exclusions)
    {
        $this->baseUrl = $baseUrl;
        $this->exclusions = $exclusions;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getExclusions(): array
    {
        return $this->exclusions;
    }

    public function isExcluded(string $url): bool
    {
        // Query strings should be ignored.
        $url = explode('?', $url)[0];

        $url = Str::removeLeft($url, $this->baseUrl);

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
