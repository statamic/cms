<?php

namespace Statamic\Sites;

use Statamic\Support\Str;

class Sites
{
    protected $config;
    protected $sites;
    protected $current;

    public function __construct($config)
    {
        $this->setConfig($config);
    }

    public function all()
    {
        return $this->sites;
    }

    public function default()
    {
        return $this->sites->first();
    }

    public function hasMultiple()
    {
        return $this->sites->count() > 1;
    }

    public function get($handle)
    {
        return $this->sites->get($handle);
    }

    public function findByUrl($url)
    {
        $url = Str::before($url, '?');
        $url = Str::ensureRight($url, '/');

        return collect($this->sites)->filter(function ($site) use ($url) {
            return Str::startsWith($url, Str::ensureRight($site->absoluteUrl(), '/'));
        })->sortByDesc->url()->first();
    }

    public function current()
    {
        return $this->current
            ?? $this->findByUrl(request()->getUri())
            ?? $this->default();
    }

    public function setCurrent($site)
    {
        $this->current = $this->get($site);
    }

    public function selected()
    {
        return $this->get(session('statamic.cp.selected-site', $this->default()->handle()));
    }

    public function setConfig($key, $value = null)
    {
        // If no value is provided, then the key must've been the entire config.
        // Otherwise, we should just replace the specific key in the config.
        if (is_null($value)) {
            $this->config = $key;
        } else {
            array_set($this->config, $key, $value);
        }

        $this->sites = $this->toSites($this->config['sites']);
    }

    protected function toSites($config)
    {
        return collect($config)->map(function ($site, $handle) {
            return new Site($handle, $site);
        });
    }
}
