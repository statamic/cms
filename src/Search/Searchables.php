<?php

namespace Statamic\Search;

use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Contracts\Taxonomies\Term as TermContract;
use Statamic\Facades\Asset;
use Statamic\Facades\Entry;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class Searchables
{
    protected $index;

    public function __construct(Index $index)
    {
        $this->index = $index;
    }

    public function all(): Collection
    {
        $searchables = $this->getConfiguredSearchables();

        if ($searchables->contains('all')) {
            return collect()
                ->merge(Entry::all())
                ->merge(Term::all())
                ->merge(Asset::all())
                ->merge(User::all());
        }

        return $searchables->flatMap(function ($item) {
            if (starts_with($item, 'collection:')) {
                $collection = str_after($item, 'collection:');

                return $collection === '*' ? Entry::all() : Entry::whereCollection($collection);
            }

            if (starts_with($item, 'taxonomy:')) {
                $taxonomy = str_after($item, 'taxonomy:');

                return $taxonomy === '*' ? Term::all() : Term::whereTaxonomy($taxonomy);
            }

            if (starts_with($item, 'assets:')) {
                $container = str_after($item, 'assets:');

                return $container === '*' ? Asset::all() : Asset::whereContainer($container);
            }

            if ($item === 'users') {
                return User::all();
            }

            throw new \LogicException("Unknown searchable [$item].");
        });
    }

    public function contains($searchable)
    {
        $searchables = $this->getConfiguredSearchables();

        if (! $this->isSearchable($searchable)) {
            return false;
        }

        if ($searchables->contains('all')) {
            return true;
        }

        if ($searchable instanceof EntryContract) {
            $collections = $this->searchableCollections();

            return $collections->isNotEmpty()
                && ($collections->contains('*') || $collections->contains($searchable->collectionHandle()));
        }

        if ($searchable instanceof TermContract) {
            $taxonomies = $this->searchableTaxonomies();

            return $taxonomies->isNotEmpty()
                && ($taxonomies->contains('*') || $taxonomies->contains($searchable->taxonomyHandle()));
        }

        if ($searchable instanceof AssetContract) {
            $containers = $this->searchableAssetContainers();

            return $containers->isNotEmpty()
                && ($containers->contains('*') || $containers->contains($searchable->containerHandle()));
        }

        if ($searchable instanceof UserContract) {
            return $searchables->contains('users');
        }

        return false;
    }

    private function getConfiguredSearchables()
    {
        return collect(Arr::wrap($this->index->config()['searchables']));
    }

    private function searchableCollections()
    {
        return $this->getConfiguredSearchables()->filter(function ($item) {
            return Str::startsWith($item, 'collection:');
        })->map(function ($item) {
            return Str::after($item, 'collection:');
        });
    }

    private function searchableTaxonomies()
    {
        return $this->getConfiguredSearchables()->filter(function ($item) {
            return Str::startsWith($item, 'taxonomy:');
        })->map(function ($item) {
            return Str::after($item, 'taxonomy:');
        });
    }

    private function searchableAssetContainers()
    {
        return $this->getConfiguredSearchables()->filter(function ($item) {
            return Str::startsWith($item, 'assets:');
        })->map(function ($item) {
            return Str::after($item, 'assets:');
        });
    }

    private function isSearchable($searchable)
    {
        $contracts = [
            EntryContract::class,
            TermContract::class,
            AssetContract::class,
            UserContract::class,
        ];

        foreach ($contracts as $contract) {
            if ($searchable instanceof $contract) {
                return true;
            }
        }

        return false;
    }

    public function fields($searchable): array
    {
        $fields = $this->index->config()['fields'];
        $transformers = $this->index->config()['transformers'] ?? [];

        return collect($fields)->mapWithKeys(function ($field) use ($searchable) {
            if (method_exists($searchable, $field)) {
                $value = $searchable->{$field}();
            } else {
                $value = $searchable instanceof EntryContract ? $searchable->value($field) : $searchable->get($field);
            }

            return [$field => $value];
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
