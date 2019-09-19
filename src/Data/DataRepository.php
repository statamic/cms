<?php

namespace Statamic\Data;

use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Routing\RouteRepository;
use Statamic\Contracts\Auth\UserRepository;
use Statamic\Contracts\Assets\AssetRepository;
use Statamic\Contracts\Taxonomies\TermRepository;
use Statamic\Contracts\Entries\EntryRepository;
use Statamic\Contracts\Globals\GlobalRepository;
use Statamic\Contracts\Taxonomies\TaxonomyRepository;

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
        list($handle, $id) = $this->splitReference($reference);

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
            list($repo, $id) = explode('::', $id, 2);
        }

        return [$repo, $id];
    }
}
