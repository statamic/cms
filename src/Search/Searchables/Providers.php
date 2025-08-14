<?php

namespace Statamic\Search\Searchables;

use Illuminate\Support\Collection;
use Statamic\Search\Index;

class Providers
{
    protected $providers = [];
    protected $prefixes = [];
    protected $groups = [];

    public function register(string $class, ?string $group = null)
    {
        $this->providers[$handle = $class::handle()] = $class;
        $this->prefixes[$class::referencePrefix()] = $handle;

        if ($group) {
            $this->pushToGroup($handle, $group);
        }

        return $this;
    }

    public function providers(?string $group = null): Collection
    {
        return $this->providers = collect($this->providers)
            ->filter(fn ($_, $handle) => $group ? $this->isProviderInGroup($handle, $group) : true)
            ->map(fn ($provider) => is_string($provider) ? app($provider) : $provider);
    }

    public function make(string $key, ?Index $index = null, ?array $keys = null)
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

    protected function pushToGroup(string $handle, string $group): void
    {
        $inGroup = $this->groups[$group] ?? [];

        $this->groups[$group] = [...$inGroup, $handle];
    }

    protected function isProviderInGroup(string $handle, string $group): bool
    {
        return collect($this->groups[$group])->contains($handle);
    }
}
