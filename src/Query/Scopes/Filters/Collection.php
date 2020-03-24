<?php

namespace Statamic\Query\Scopes\Filters;

use Statamic\Facades;
use Statamic\Query\Scopes\Filter;

class Collection extends Filter
{
    public static function title()
    {
        return __('Collection');
    }

    public function fieldItems()
    {
        return [
            'value' => [
                'display' => __('Collection'),
                'type' => 'select',
                'options' => $this->options()->all(),
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

    protected function options()
    {
        return collect($this->context['collections'])->mapWithKeys(function ($collection) {
            return [$collection => Facades\Collection::findByHandle($collection)->title()];
        });
    }
}
