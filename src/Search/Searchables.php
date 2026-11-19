<?php

namespace Statamic\Search;

use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Statamic\Contracts\Search\Searchable;
use Statamic\Exceptions\AllSearchablesNotSupported;
use Statamic\Search\Searchables\Providers;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class Searchables
{
    protected $index;
    protected $providers;
    protected $manager;
    protected static array $cpSearchables = [];
    protected static array $contentSearchables = [];

    public function __construct(Index $index)
    {
        $this->index = $index;
        $this->providers = $this->makeProviders();
    }

    public static function addCpSearchable($searchable)
    {
        if (method_exists($searchable, 'handle')) {
            $searchable = "{$searchable::handle()}:*";
        }

        static::$cpSearchables[] = $searchable;
    }

    public static function clearCpSearchables()
    {
        static::$cpSearchables = [];
    }

    public static function addContentSearchable($searchable)
    {
        if (method_exists($searchable, 'handle')) {
            $searchable = "{$searchable::handle()}:*";
        }

        static::$contentSearchables[] = $searchable;
    }

    public static function clearContentSearchables()
    {
        static::$contentSearchables = [];
    }

    private function makeProviders()
    {
        $manager = app(Providers::class);

        $providers = collect(Arr::wrap($this->index->config()['searchables'] ?? []));

        if ($providers->contains('all')) {
            throw new AllSearchablesNotSupported();
        }

        return $providers
            ->flatMap(function ($key) {
                if ($key === 'content') {
                    return ['collection:*', 'taxonomy:*', 'assets:*', ...static::$contentSearchables];
                }

                if ($key === 'addons') {
                    return static::$cpSearchables;
                }

                return [$key];
            })
            ->map(fn ($key) => ['provider' => Str::before($key, ':'), 'key' => Str::after($key, ':')])
            ->groupBy('provider')
            ->map(fn ($items, $provider) => $manager->make($provider, $this->index, $items->map->key->all()));
    }

    public function all(): Collection
    {
        return $this->providers->flatMap->provide();
    }

    public function lazy(): LazyCollection
    {
        return LazyCollection::make(function () {
            foreach ($this->providers as $provider) {
                yield $provider->provide();
            }
        });
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
