<?php

namespace Statamic\Search\Searchables;

use Illuminate\Support\Collection;

class Providers
{
    protected $providers;
    protected $prefixes;

    public function __construct()
    {
        $this->providers = Collection::make();
    }

    public function register(string $name, $class)
    {
        $this->providers[$name] = $class;
        $this->prefixes[$class->referencePrefix()] = $name;

        return $this;
    }

    public function providers(): Collection
    {
        return $this->providers;
    }

    public function make(string $key, array $keys = null)
    {
        if (! $provider = $this->providers()->get($key)) {
            throw new \Exception('Unknown searchable ['.$key.']');
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
