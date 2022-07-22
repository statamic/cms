<?php

namespace Statamic\Data;

use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class DataRepository
{
    protected $repositories = [];

    public function setRepository($handle, $class)
    {
        $this->repositories[$handle] = $class;

        return $this;
    }

    public function find($reference)
    {
        if (! $reference) {
            return null;
        }

        [$handle, $id] = $this->splitReference($reference);

        if (! $handle) {
            return $this->attemptAllRepositories('find', $id);
        }

        if (! $class = Arr::get($this->repositories, $handle)) {
            return null;
        }

        return app($class)->find($id);
    }

    public function findByUri($uri, $site = null)
    {
        return $this->attemptAllRepositories('findByUri', $uri, $site);
    }

    public function findByRequestUrl($url)
    {
        if (! $site = Site::findByUrl($url)) {
            return null;
        }

        $url = $site->relativePath($url);

        if ($this->isAmpUrl($url)) {
            $url = Str::ensureLeft(Str::after($url, '/'.config('statamic.amp.route')), '/');
        }

        if (Str::contains($url, '?')) {
            $url = substr($url, 0, strpos($url, '?'));
        }

        if (Str::endsWith($url, '/') && Str::length($url) > 1) {
            $url = rtrim($url, '/');
        }

        return $this->findByUri($url, $site->handle());
    }

    private function isAmpUrl($url)
    {
        if (! config('statamic.amp.enabled')) {
            return false;
        }

        $url = URL::makeRelative($url);

        return Str::startsWith($url, '/'.config('statamic.amp.route'));
    }

    protected function attemptAllRepositories($method, ...$args)
    {
        foreach ($this->repositories as $class) {
            if (! method_exists($class, $method)) {
                continue;
            }

            if ($result = app($class)->$method(...$args)) {
                return $result;
            }
        }
    }

    public function splitReference($reference)
    {
        $repo = null;
        $id = $reference;

        if (substr_count($id, '::')) {
            [$repo, $id] = explode('::', $id, 2);
        }

        if ($repo && ! isset($this->repositories[$repo])) {
            return [null, $reference];
        }

        return [$repo, $id];
    }
}
