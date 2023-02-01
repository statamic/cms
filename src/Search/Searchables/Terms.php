<?php

namespace Statamic\Search\Searchables;

use Illuminate\Support\Collection;
use Statamic\Contracts\Taxonomies\Term as TermContract;
use Statamic\Facades\Term;
use Statamic\Support\Str;

class Terms extends Provider
{
    public static function handle(): string
    {
        return 'taxonomy';
    }

    public static function referencePrefix(): string
    {
        return 'term';
    }

    public function provide(): Collection
    {
        $query = Term::query();

        if (! $this->usesWildcard()) {
            $query->whereIn('taxonomy', $this->keys);
        }

        if ($site = $this->index->locale()) {
            $query->where('site', $site);
        }

        return $query->get()->filter($this->filter())->values();
    }

    public function contains($searchable): bool
    {
        if (! $searchable instanceof TermContract) {
            return false;
        }

        if (! $this->usesWildcard() && ! in_array($searchable->taxonomyHandle(), $this->keys)) {
            return false;
        }

        if (($site = $this->index->locale()) && $site !== $searchable->locale()) {
            return false;
        }

        return $this->filter()($searchable);
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
