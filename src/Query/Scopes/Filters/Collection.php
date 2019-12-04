<?php

namespace Statamic\Query\Scopes\Filters;

use Statamic\Facades;
use Statamic\Query\Scopes\Filter;

class Collection extends Filter
{
    public function fieldItems()
    {
        $options = collect($this->context['collections'])->mapWithKeys(function ($collection) {
            return [$collection => Facades\Collection::findByHandle($collection)->title()];
        })->all();

        return [
            'value' => [
                'display' => __('Collection'),
                'type' => 'select',
                'options' => $options,
                'clearable' => true,
                'multiple' => true,
            ],
        ];
    }

    public function apply($query, $values)
    {
        $collections = $values['value'];

        if (empty($collections)) {
            $collections = $this->context['collections'];
        }

        $query->whereIn('collection', $collections);
    }

    public function visibleTo($key)
    {
        return $key === 'entries-fieldtype' && count($this->context['collections']) > 1;
    }
}
