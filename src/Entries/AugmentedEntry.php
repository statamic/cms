<?php

namespace Statamic\Entries;

use Statamic\Data\AbstractAugmented;
use Statamic\Facades\Collection;
use Statamic\Statamic;

class AugmentedEntry extends AbstractAugmented
{
    public function keys()
    {
        return $this->data->values()->keys()
            ->merge($this->data->supplements()->keys())
            ->merge($this->commonKeys())
            ->merge($this->blueprintFields()->keys())
            ->unique()->sort()->values()->all();
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
            'amp_url',
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
        return $this->data->value('mount') ?? Collection::findByMount($this->data);
    }

    public function authors()
    {
        return $this->wrapValue($this->getFromData('authors'), 'authors');
    }

    public function originId()
    {
        return optional($this->data->origin())->id();
    }
}
