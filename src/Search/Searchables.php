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
    protected $providers;
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
            return $manager->providers()->map(fn ($_, $key) => $manager->make($key, $this->index, ['*']));
        }

        return $providers
            ->map(fn ($key) => ['provider' => Str::before($key, ':'), 'key' => Str::after($key, ':')])
            ->groupBy('provider')
            ->map(fn ($items, $provider) => $manager->make($provider, $this->index, $items->map->key->all()));
    }

    public function all(): Collection
    {
        return $this->providers->flatMap->provide();
    }

    public function contains($searchable)
    {
        foreach ($this->providers as $provider) {
            if ($provider->contains($searchable)) {
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
        })->flatMap(function ($value, $field) use ($transformers, $searchable) {
            if (! $transformer = $transformers[$field] ?? null) {
                return [$field => $value];
            }

            $value = $this->transformValue($transformer, $field, $value, $searchable);

            return is_array($value) ? $value : [$field => $value];
        })->all();
    }

    private function transformValue($transformer, $field, $value, $searchable)
    {
        if ($transformer instanceof Closure) {
            return $transformer($value, $searchable);
        }

        try {
            return app($transformer)->handle($value, $field, $searchable);
        } catch (BindingResolutionException $e) {
            throw new \LogicException("Search transformer [{$transformer}] not found.");
        }
    }
}
