<?php

namespace Statamic\Search\Searchables;

use Illuminate\Support\Collection;
use Statamic\Search\Index;

class Providers
{
    protected $providers;
    protected $prefixes;

    public function __construct()
    {
        $this->providers = Collection::make();
    }

    public function register($class)
    {
        $this->providers[$handle = $class::handle()] = $class;
        $this->prefixes[$class::referencePrefix()] = $handle;

        return $this;
    }

    public function providers(): Collection
    {
        return $this->providers = $this->providers->map(function ($provider) {
            return is_string($provider) ? app($provider) : $provider;
        });
    }

    public function make(string $key, Index $index = null, array $keys = null)
    {
        if (! $provider = $this->providers()->get($key)) {
            throw new \Exception('Unknown searchable ['.$key.']');
        }

        if ($index) {
            $provider->setIndex($index);
        }

        if ($keys) {
            $provider->setKeys($keys);
        }

        return $provider;
    }

    public function getByPrefix(string $prefix)
    {
        return $this->providers()->get($this->prefixes[$prefix]);
    }
}
