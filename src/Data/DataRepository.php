<?php

namespace Statamic\Data;

use Statamic\Support\Arr;

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
