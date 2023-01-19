<?php

namespace Statamic\Search\Searchables;

use Illuminate\Support\Collection;
use Statamic\Contracts\Taxonomies\Term as TermContract;
use Statamic\Facades\Term;
use Statamic\Support\Str;

class Terms extends Provider
{
    public function referencePrefix(): string
    {
        return 'term';
    }

    public function provide(): Collection
    {
        $terms = $this->usesWildcard()
            ? Term::all()
            : Term::query()->whereIn('taxonomy', $this->keys)->get();

        return $terms->filter($this->filter());
    }

    public function contains($searchable): bool
    {
        if (! $searchable instanceof TermContract) {
            return false;
        }

        if (! $this->usesWildcard() && ! in_array($searchable->taxonomyHandle(), $this->keys)) {
            return false;
        }

        return $this->filter()($searchable);
    }

    public function isSearchable($searchable): bool
    {
        return $searchable instanceof TermContract;
    }

    public function find(array $refs): Collection
    {
        $ids = collect($refs)
            ->groupBy(fn ($ref) => Str::beforeLast($ref, '::'))
            ->keys()->all();

        // References are provided to this method without the prefix.
        $refs = collect($refs)->map(fn ($ref) => $this->referencePrefix().'::'.$ref);

        $terms = Term::query()->whereIn('id', $ids)->get();

        // Terms would be returned from the query with all localizations, but
        // they might not have all beeen requested, so we'll filter them out.
        return $terms
            ->filter(fn ($term) => $refs->contains($term->reference()))
            ->values();
    }
}
