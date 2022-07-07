<?php

namespace Statamic\Search\Searchables;

use Illuminate\Support\Collection;
use Statamic\Contracts\Taxonomies\Term as TermContract;
use Statamic\Facades\Term;

class Taxonomies extends Provider
{
    public function provide(): Collection
    {
        if ($this->usesWildcard()) {
            return Term::all();
        }

        return Term::query()->whereIn('taxonomy', $this->keys)->get();
    }

    public function contains($searchable): bool
    {
        if (! $searchable instanceof TermContract) {
            return false;
        }

        return $this->usesWildcard() || in_array($searchable->taxonomyHandle(), $this->keys);
    }

    public function isSearchable($searchable): bool
    {
        return $searchable instanceof TermContract;
    }
}
