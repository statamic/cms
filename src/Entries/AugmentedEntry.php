<?php

namespace Statamic\Entries;

use Statamic\Data\AbstractAugmented;
use Statamic\Facades\Collection;
use Statamic\Statamic;

class AugmentedEntry extends AbstractAugmented
{
    private $cachedKeys;

    public function keys()
    {
        if ($this->cachedKeys) {
            return $this->cachedKeys;
        }

        $keys = array_merge(
            collect($this->data->keys())->all(),
            array_keys(collect($this->data->supplements())->all()),
            $this->commonKeys(),
            array_keys($this->blueprintFields()->all()),
        );

        $keys = array_unique($keys, SORT_REGULAR);
        asort($keys, SORT_REGULAR);

        return $this->cachedKeys = array_values($keys);
    }

    private function commonKeys()
    {
        return [
            'id',
            'origin_id',
            'slug',
            'uri',
            'url',
            'edit_url',
            'permalink',
            'api_url',
            'status',
            'published',
            'private',
            'date',
            'order',
            'is_entry',
            'collection',
            'blueprint',
            'mount',
            'locale',
            'last_modified',
            'updated_at',
            'updated_by',
        ];
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

    protected function isEntry()
    {
        return true;
    }

    protected function permalink()
    {
        return $this->data->absoluteUrl();
    }

    protected function parent()
    {
        $parent = $this->data->parent();

        return Statamic::isApiRoute()
            ? optional($parent)->toShallowAugmentedCollection()
            : $parent;
    }

    protected function mount()
    {
        $mount = $this->data->value('mount') ?? Collection::findByMount($this->data);

        if (! $mount && ($origin = $this->data->origin())) {
            return Collection::findByMount($origin);
        }

        return $mount;
    }

    public function authors()
    {
        return $this->wrapValue($this->getFromData('authors'), 'authors');
    }

    public function originId()
    {
        return optional($this->data->origin())->id();
    }

    public function date()
    {
        if (! $this->data->collection()->dated()) {
            return $this->wrapValue($this->getFromData('date'), 'date');
        }

        // Return a copy, not the entry's live `date` instance. Carbon is mutable, and this
        // value gets augmented (and potentially mutated by modifiers, e.g. via `title_format`)
        // before the entry is saved, which would otherwise corrupt the entry's actual date.
        return optional($this->data->date())->copy();
    }
}
