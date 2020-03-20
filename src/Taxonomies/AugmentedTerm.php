<?php

namespace Statamic\Taxonomies;

use Statamic\Data\AbstractAugmented;

class AugmentedTerm extends AbstractAugmented
{
    protected function keys()
    {
        return $this->data->data()->keys()
            ->merge([
                'id',
                'slug',
                'uri',
                'url',
                'title',
                'is_term',
                'entries',
                'entries_count',
                'api_url',
            ])->all();
    }

    protected function entries()
    {
        return $this->data->queryEntries()->where('site', $this->data->locale());
    }

    protected function entriesCount()
    {
        return $this->entries()->count();
    }

    protected function isTerm()
    {
        return true;
    }
}
