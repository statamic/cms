<?php

namespace Statamic\Stache\Indexes\Terms;

use ReflectionMethod;
use Statamic\Facades\Entry;
use Statamic\Facades\Term;
use Statamic\Stache\Indexes\Value as Index;
use Statamic\Stache\Repositories\TermRepository;
use Statamic\Taxonomies\LocalizedTerm;

class Value extends Index
{
    private ?array $publishedEntries = null;

    public function getItemValue($item)
    {
        // The augmented `entries_count` value only considers published entries, so
        // when filtering (e.g. via the `min_count` param on the taxonomy tag) we
        // need to resolve the same published-only count for consistency.
        if ($this->name === 'entries_count') {
            return $this->publishedEntries === null
                ? Term::entriesCount($item, 'published')
                : $this->countPublishedEntries($item);
        }

        return parent::getItemValue($item);
    }

    public function getItems()
    {
        // Counting a term's published entries is a query over every entry. Asked for one term
        // at a time that's a query per term, so which entries are published is found once
        // for the whole index and each term is counted from that.
        if ($this->name === 'entries_count' && $this->countsEntriesTheUsualWay()) {
            $this->publishedEntries = $this->publishedEntries();
        }

        try {
            return $this->getTermItems();
        } finally {
            $this->publishedEntries = null;
        }
    }

    private function getTermItems()
    {
        // There's an association for every entry that uses a term, so most terms have
        // many. They all make the same term, so it only needs making once per site.
        $associations = [];

        foreach ($this->store->index('associations')->items() as $association) {
            if ($association) {
                $associations[$association['site'].'::'.$association['slug']] = $association;
            }
        }

        $associatedItems = [];

        foreach ($associations as $association) {
            $term = Term::make($value = $association['slug'])
                ->taxonomy($this->store->childKey())
                ->set('title', $value)
                ->in($association['site']);

            $associatedItems[$term->locale().'::'.$term->slug()] = $this->getItemValue($term);
        }

        return collect($associatedItems)
            ->merge(parent::getItems())
            ->all();
    }

    private function publishedEntries(): array
    {
        $ids = [];

        foreach ($this->store->index('associations')->items() as $association) {
            if ($association) {
                $ids[$association['entry']] = true;
            }
        }

        return Entry::query()
            ->whereIn('id', array_keys($ids))
            ->whereStatus('published')
            ->pluck('id')
            ->flip()
            ->all();
    }

    /**
     * The same count as TermRepository::entriesCount($term, 'published').
     */
    private function countPublishedEntries($term): int
    {
        if ($term->collection()) {
            return Term::entriesCount($term, 'published');
        }

        $entries = [];

        foreach ($this->store->index('associations')->forValue($term->inDefaultLocale()->slug()) as $association) {
            if ($term instanceof LocalizedTerm && $association['site'] !== $term->locale()) {
                continue;
            }

            if (isset($this->publishedEntries[$association['entry']])) {
                $entries[$association['entry']] = true;
            }
        }

        return count($entries);
    }

    /**
     * Whether a term's entries are counted by the method this index's own counting matches.
     */
    private function countsEntriesTheUsualWay(): bool
    {
        return (new ReflectionMethod(Term::getFacadeRoot(), 'entriesCount'))->getDeclaringClass()->getName() === TermRepository::class;
    }
}
