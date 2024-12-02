<?php

namespace Statamic\Stache\Query;

use Illuminate\Support\Collection;
use Statamic\Exceptions\InvalidQueryDateException;
use Statamic\Query\EmptyQueryBuilder;

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
            ->each(function ($collection) use ($query, $status) {
                try {
                    return $query->orWhere(fn ($q) => $this->addCollectionStatusLogicToQuery($q, $status, $collection));
                } catch (InvalidQueryDateException $e) {
                    return new EmptyQueryBuilder();
                }
            })
        );
    }

    private function addCollectionStatusLogicToQuery($query, $status, $collection): void
    {
        $this->addCollectionWhereToStatusQuery($query, $collection->handle());

        if (! $collection->dated() || ($collection->futureDateBehavior() === 'public' && $collection->pastDateBehavior() === 'public')) {
            if ($status === 'scheduled' || $status === 'expired') {
                throw new InvalidQueryDateException(); // intentionally trigger no results.
            }

            return;
        }

        if ($collection->futureDateBehavior() === 'private') {
            $status === 'scheduled'
                ? $query->where('date', '>', now())
                : $query->where('date', '<', now());

            if ($status === 'expired') {
                throw new InvalidQueryDateException(); // intentionally trigger no results.
            }
        }

        if ($collection->pastDateBehavior() === 'private') {
            $status === 'expired'
                ? $query->where('date', '<', now())
                : $query->where('date', '>', now());

            if ($status === 'scheduled') {
                throw new InvalidQueryDateException(); // intentionally trigger no results.
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
