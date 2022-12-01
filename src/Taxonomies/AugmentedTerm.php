<?php

namespace Statamic\Taxonomies;

use Statamic\Data\AbstractAugmented;
use Statamic\Statamic;

class AugmentedTerm extends AbstractAugmented
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
            'permalink',
            'title',
            'is_term',
            'entries',
            'entries_count',
            'api_url',
            'taxonomy',
            'edit_url',
            'locale',
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
        return $this->data->absoluteUrl();
    }

    public function title()
    {
        $title = $this->data->getSupplement('title') ?? $this->data->title();

        return $this->wrapValue($title, 'title');
    }
}
