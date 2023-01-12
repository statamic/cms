<?php

namespace Statamic\Search;

use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Statamic\Contracts\Search\Searchable;
use Statamic\Search\Searchables\Providers;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class Searchables
{
    protected $index;
    protected $manager;

    public function __construct(Index $index)
    {
        $this->index = $index;
        $this->providers = $this->makeProviders();
    }

    private function makeProviders()
    {
        $manager = app(Providers::class);

        $providers = collect(Arr::wrap($this->index->config()['searchables'] ?? []));

        if ($providers->contains('all')) {
            return $manager->providers()->map(fn ($_, $key) => $manager->make($key, ['*']));
        }

        return $providers
            ->map(fn ($key) => ['provider' => Str::before($key, ':'), 'key' => Str::after($key, ':')])
            ->groupBy('provider')
            ->map(fn ($items, $provider) => $manager->make($provider, $items->map->key->all()));
    }

    public function all(): Collection
    {
        return $this->providers->flatMap->provide();
    }

    public function contains($searchable)
    {
        if (! $this->isSearchable($searchable)) {
            return false;
        }

        foreach ($this->providers as $provider) {
            if ($provider->contains($searchable)) {
                return true;
            }
        }

        return false;
    }

    private function isSearchable($searchable)
    {
        foreach ($this->providers as $provider) {
            if ($provider->isSearchable($searchable)) {
                return true;
            }
        }

        return false;
    }

    public function fields(Searchable $searchable): array
    {
        $fields = $this->index->config()['fields'];
        $transformers = $this->index->config()['transformers'] ?? [];

        return collect($fields)->mapWithKeys(function ($field) use ($searchable) {
            return [$field => $searchable->getSearchValue($field)];
        })->flatMap(function ($value, $field) use ($transformers) {
            if (! $transformer = $transformers[$field] ?? null) {
                return [$field => $value];
            }

            $value = $this->transformValue($transformer, $field, $value);

            return is_array($value) ? $value : [$field => $value];
        })->all();
    }

    private function transformValue($transformer, $field, $value)
    {
        if ($transformer instanceof Closure) {
            return $transformer($value);
        }

        try {
            return app($transformer)->handle($value, $field);
        } catch (BindingResolutionException $e) {
            throw new \LogicException("Search transformer [{$transformer}] not found.");
        }
    }
}
