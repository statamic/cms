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
            'collections' => [
                'placeholder' => __('Select Collection(s)'),
                'type' => 'select',
                'options' => $this->options()->all(),
                'clearable' => true,
                'multiple' => true,
            ],
        ];
    }

    public function apply($query, $values)
    {
        $query->whereIn('collection', $values['collections']);
    }

    public function badge($values)
    {
        return __('Collections').': '.collect($values['collections'])->implode(', ');
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
