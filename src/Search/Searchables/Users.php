<?php

namespace Statamic\Search\Searchables;

use Illuminate\Support\Collection;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Facades\User;

class Users extends Provider
{
    public function referencePrefix(): string
    {
        return 'user';
    }

    public function provide(): Collection
    {
        return User::all()->filter($this->filter());
    }

    public function contains($searchable): bool
    {
        if (! $searchable instanceof UserContract) {
            return false;
        }

        return $this->filter()($searchable);
    }

    public function find(array $ids): Collection
    {
        return User::query()->whereIn('id', $ids)->get();
    }
}
