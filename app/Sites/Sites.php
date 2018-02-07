<?php

namespace Statamic\Sites;

use Statamic\API\Str;


class Sites
{
    protected $default;
    protected $sites;
    protected $current;

    public function __construct($config)
    {
        $this->default = $config['default'];
        $this->sites = $this->toSites($config['sites']);
    }

    public function all()
    {
        return $this->sites;
    }

    public function get($handle)
    {
        return $this->sites->get($handle);
    }

    public function findByUrl($url)
    {
        $url = Str::ensureRight($url, '/');

        return collect($this->sites)->filter(function ($site) use ($url) {
            return Str::startsWith($url, $site->url());
        })->sortByDesc->url()->first();
    }

    public function current()
    {
        return $this->current
            ?? $this->findByUrl(request()->getUri())
            ?? $this->get($this->default);
    }

    public function setCurrent($site)
    {
        $this->current = $this->get($site);
    }

    protected function toSites($config)
    {
        return collect($config)->map(function ($site, $handle) {
            return new Site($handle, $site);
        });
    }
}