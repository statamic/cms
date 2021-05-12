<?php

namespace Tests\Fakes;

use Statamic\Fields\Blueprint;
use Statamic\Fields\BlueprintRepository;
use Statamic\Support\Arr;

class FakeBlueprintRepository extends BlueprintRepository
{
    protected $repo;
    protected $blueprints;

    public function __construct($repo)
    {
        $this->blueprints = [];

        $this->repo = $repo->setDirectory(__DIR__.'/../__fixtures__/dev-null/blueprints');
    }

    public function find($blueprint): ?Blueprint
    {
        $parts = explode('.', $blueprint);
        $handle = array_pop($parts);
        $namespace = implode('.', $parts);
        $namespace = empty($namespace) ? '*' : $namespace;

        if ($found = Arr::get($this->blueprints[$namespace] ?? [], $handle)) {
            // Return a clone so that modifications to the object will only be updated when saving.
            return clone $found;
        }

        // Fall back to the actual repo, so it can find fallbacks.
        return $this->repo->find($handle);
    }

    public function save(Blueprint $blueprint)
    {
        $this->blueprints[$blueprint->namespace() ?? '*'][$blueprint->handle()] = $blueprint;
    }

    public function delete(Blueprint $blueprint)
    {
        $namespace = $blueprint->namespace() ?? '*';
        $blueprints = $this->blueprints[$namespace];
        unset($blueprints[$blueprint->handle()]);
        $this->blueprints = $blueprints;
    }

    public function in($namespace)
    {
        return collect($this->blueprints[str_replace('/', '.', $namespace)] ?? []);
    }
}
