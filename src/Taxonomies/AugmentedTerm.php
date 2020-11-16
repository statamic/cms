<?php

namespace Statamic\Taxonomies;

use Statamic\Data\AbstractAugmented;

class AugmentedTerm extends AbstractAugmented
{
    public function keys()
    {
        return $this->data->values()->keys()
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

    protected function entries()
    {
        return $this->data->queryEntries()->where('site', $this->data->locale());
    }

    protected function isTerm()
    {
        return true;
    }

    protected function permalink()
    {
        return $this->get('absolute_url');
    }
}
