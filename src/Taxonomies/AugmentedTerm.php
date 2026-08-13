<?php

namespace Statamic\Taxonomies;

use Statamic\Data\AbstractAugmented;
use Statamic\Facades\Blink;
use Statamic\Facades\Term;
use Statamic\Query\StatusQueryBuilder;
use Statamic\Statamic;

class AugmentedTerm extends AbstractAugmented
{
    private $cachedKeys;

    public function keys()
    {
        if ($this->cachedKeys) {
            return $this->cachedKeys;
        }

        return $this->cachedKeys = $this->data->values()->keys()
            ->merge($this->data->supplements()->keys())
            ->merge($this->commonKeys())
            ->merge($this->blueprintFields()->keys())
            ->unique()->sort()->values()->all();
    }

    private function commonKeys()
    {
        return [
            'id',
            'slug',
            'uri',
            'url',
            'permalink',
            'title',
            'is_term',
            'entries',
            'entries_count',
            'api_url',
            'taxonomy',
            'edit_url',
            'locale',
            'collection',
            'updated_at',
            'updated_by',
            'parent',
            'children',
            'ancestors',
            'depth',
            'is_root',
        ];
    }

    protected function parent()
    {
        return $this->data->parent();
    }

    protected function children()
    {
        return $this->data->children();
    }

    protected function ancestors()
    {
        return $this->data->ancestors();
    }

    protected function depth()
    {
        return $this->data->depth();
    }

    protected function isRoot()
    {
        if (! $depth = $this->data->depth()) {
            return null;
        }

        return $depth === 1;
    }

    protected function updatedBy()
    {
        $user = $this->data->lastModifiedBy();

        return Statamic::isApiRoute()
            ? optional($user)->toShallowAugmentedCollection()
            : $user;
    }

    protected function updatedAt()
    {
        return $this->data->lastModified();
    }

    protected function entries()
    {
        return (new StatusQueryBuilder($this->data->queryEntries()))->where('site', $this->data->locale());
    }

    protected function isTerm()
    {
        return true;
    }

    protected function permalink()
    {
        return $this->data->absoluteUrl();
    }

    public function title()
    {
        $title = $this->data->getSupplement('title') ?? $this->data->title();

        return $this->wrapValue($title, 'title');
    }

    public function entriesCount()
    {
        $key = vsprintf('term-published-entries-count-%s-%s', [
            $this->data->id(),
            optional($this->data->collection())->handle(),
        ]);

        return Blink::once($key, function () {
            return Term::entriesCount($this->data, 'published');
        });
    }
}
