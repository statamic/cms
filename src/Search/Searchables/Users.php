<?php

namespace Statamic\Search\Searchables;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Facades\User;

class Users extends Provider
{
    public static function handle(): string
    {
        return 'users';
    }

    public static function referencePrefix(): string
    {
        return 'user';
    }

    public function provide(): Collection|LazyCollection
    {
        $query = User::query();

        $this->applyQueryScope($query);

        if ($filter = $this->filter()) {
            return $query
                ->lazy(config('statamic.search.chunk_size'))
                ->filter($filter)
                ->values()
                ->map->reference();
        }

        return $query->pluck('reference');
    }

    public function contains($searchable): bool
    {
        if (! $searchable instanceof UserContract) {
            return false;
        }

        if ($filter = $this->filter()) {
            return $filter($searchable);
        }

        $query = User::query()->where('id', $searchable->id());

        $this->applyQueryScope($query);

        return $query->exists();
    }

    public function find(array $ids): Collection
    {
        return User::query()->whereIn('id', $ids)->get();
    }
}
