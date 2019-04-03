<?php

namespace Statamic\Search;

use Statamic\API\Asset;
use Statamic\API\Entry;
use Statamic\API\Content;
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
            return Content::all();
        }

        return $searchables->flatMap(function ($item) {
            if (starts_with($item, 'collection:')) {
                return Entry::whereCollection(str_after($item, 'collection:'));
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
