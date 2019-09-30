<?php

namespace Statamic\Search;

use Statamic\Facades\Term;
use Statamic\Facades\User;
use Statamic\Facades\Asset;
use Statamic\Facades\Entry;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Searchables
{
    protected $index;

    public function __construct(Index $index)
    {
        $this->index = $index;
    }

    public function all(): Collection
    {
        $searchables = collect(Arr::wrap($this->index->config()['searchables']));

        if ($searchables->contains('all')) {
            return collect()
                ->merge(Entry::all())
                ->merge(Term::all())
                ->merge(Asset::all())
                ->merge(User::all());
        }

        return $searchables->flatMap(function ($item) {
            if (starts_with($item, 'collection:')) {
                return Entry::whereCollection(str_after($item, 'collection:'));
            }

            if (starts_with($item, 'taxonomy:')) {
                return Term::whereTaxonomy(str_after($item, 'taxonomy:'));
            }

            if (starts_with($item, 'assets:')) {
                return Asset::whereContainer(str_after($item, 'assets:'));
            }

            throw new \LogicException("Unknown searchable [$item].");
        });
    }

    public function contains($searchable)
    {
        return $this->all()->has($searchable->id());
    }

    public function fields($searchable): array
    {
        $fields = $this->index->config()['fields'];

        return collect($fields)->mapWithKeys(function ($field) use ($searchable) {
            $value = method_exists($searchable, $field) ? $searchable->{$field}() : $searchable->get($field);
            return [$field => $value];
        })->all();
    }
}
