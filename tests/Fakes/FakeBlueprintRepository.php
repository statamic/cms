<?php

namespace Tests\Fakes;

use Statamic\Fields\Blueprint;

class FakeBlueprintRepository
{
    protected $blueprints;

    public function __construct()
    {
        $this->blueprints = collect();
    }

    public function find(string $handle): ?Blueprint
    {
        if ($blueprint = array_get($this->blueprints, $handle)) {
            // Return a clone so that modifications to the object will only be updated when saving.
            return clone $blueprint;
        }

        return null;
    }

    public function save(Blueprint $blueprint)
    {
        $this->blueprints[$blueprint->handle()] = $blueprint;
    }

    public function all()
    {
        return $this->blueprints;
    }
}
