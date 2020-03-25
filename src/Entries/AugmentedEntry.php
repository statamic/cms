<?php

namespace Statamic\Entries;

use Statamic\Data\AbstractAugmented;

class AugmentedEntry extends AbstractAugmented
{
    protected function keys()
    {
        return $this->data->data()->keys()
            ->merge($this->data->supplements()->keys())
            ->merge([
                'id',
                'slug',
                'uri',
                'url',
                'edit_url',
                'permalink',
                'amp_url',
                'api_url',
                'published',
                'private',
                'date',
                'is_entry',
                'collection',
                'last_modified',
                'updated_at',
                'updated_by',
            ])->all();
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
        return $this->get('absolute_url');
    }
}
