<?php

namespace Statamic\Stache\Query;

use Illuminate\Support\Collection;

trait QueriesEntryStatus
{
    public function whereStatus(string $status)
    {
        if ($status === 'any') {
            return $this;
        }

        if (! in_array($status, ['published', 'draft', 'scheduled', 'expired'])) {
            throw new \Exception("Invalid status [$status]");
        }

        $this->ensureCollectionsAreQueriedForStatusQuery();

        if ($status === 'draft') {
            return $this->where('published', false);
        }

        $this->where('published', true);

        return $this->where(fn ($query) => $this
            ->getCollectionsForStatusQuery()
            ->each(fn ($collection) => $query->orWhere(fn ($q) => $this->addCollectionStatusLogicToQuery($q, $status, $collection))));
    }

    private function addCollectionStatusLogicToQuery($query, $status, $collection): void
    {
        $this->addCollectionWhereToStatusQuery($query, $collection->handle());

        if (! $collection->dated() || ($collection->futureDateBehavior() === 'public' && $collection->pastDateBehavior() === 'public')) {
            if ($status === 'scheduled' || $status === 'expired') {
                $query->where('date', 'invalid'); // intentionally trigger no results.
            }

            return;
        }

        if ($collection->futureDateBehavior() === 'private') {
            $status === 'scheduled'
                ? $query->where('date', '>', now())
                : $query->where('date', '<', now());

            if ($status === 'expired') {
                $query->where('date', 'invalid'); // intentionally trigger no results.
            }
        }

        if ($collection->pastDateBehavior() === 'private') {
            $status === 'expired'
                ? $query->where('date', '<', now())
                : $query->where('date', '>', now());

            if ($status === 'scheduled') {
                $query->where('date', 'invalid'); // intentionally trigger no results.
            }
        }
    }

    protected function addCollectionWhereToStatusQuery($query, $collection): void
    {
        $query->where('collection', $collection);
    }

    abstract protected function ensureCollectionsAreQueriedForStatusQuery(): void;

    abstract protected function getCollectionsForStatusQuery(): Collection;
}
