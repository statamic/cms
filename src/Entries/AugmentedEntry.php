<?php

namespace Statamic\Entries;

use Statamic\Data\AbstractAugmented;
use Statamic\Facades\Collection;

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
            'mount',
            'locale',
            'last_modified',
            'updated_at',
            'updated_by',
        ];
    }

    protected function updatedBy()
    {
        return $this->data->lastModifiedBy();
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
        return $this->data->parent();
    }

    protected function mount()
    {
        return $this->data->value('mount') ?? Collection::findByMount($this->data);
    }

    public function authors()
    {
        return $this->wrapValue($this->getFromData('authors'), 'authors');
    }
}
