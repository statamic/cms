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
                // TODO: Would prefer to use a select field here, but there's an issue with the reset-on-options-change
                // prop that causes everything to be reset every time a new value is selected.
                'type' => 'checkboxes',
                'options' => $options
            ]
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
        return 'entries-fieldtype' && count($this->context['collections']) > 1;
    }
}
