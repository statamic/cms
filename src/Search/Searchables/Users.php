<?php

namespace Statamic\Search\Searchables;

use Illuminate\Support\Collection;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Facades\User;

class Users extends Provider
{
    public function provide(): Collection
    {
        return User::all();
    }

    public function contains($searchable): bool
    {
        return $searchable instanceof UserContract;
    }

    public function isSearchable($searchable): bool
    {
        return $searchable instanceof UserContract;
    }
}
