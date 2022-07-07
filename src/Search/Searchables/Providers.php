<?php

namespace Statamic\Search\Searchables;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;

class Providers
{
    protected $providers;

    public function __construct()
    {
        $this->providers = Collection::make();
    }

    public function register(string $name, string $class)
    {
        $this->providers[$name] = $class;

        return $this;
    }

    public function providers(): Collection
    {
        return $this->providers;
    }

    public function make(string $key, array $keys)
    {
        try {
            $provider = $this->providers()->get($key);

            return app()->make($provider)->setKeys($keys);
        } catch (BindingResolutionException $e) {
            throw new \Exception('Unknown searchable ['.$key.']');
        }
    }
}
