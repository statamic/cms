<?php

namespace Statamic\StaticCaching;

use Statamic\Facades\URL;
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
        $url = URL::removeQueryAndFragment($url);

        $url = Str::removeLeft($url, $this->baseUrl);

        foreach ($this->exclusions as $excluded) {
            if (Str::endsWith($excluded, '*') && Str::startsWith($url, Str::removeRight($excluded, '*'))) {
                return true;
            } elseif (URL::tidy($url) === URL::tidy($excluded)) {
                return true;
            }
        }

        return false;
    }
}
