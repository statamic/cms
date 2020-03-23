<?php

namespace Statamic\Query\Scopes\Filters;

use Statamic\Facades;
use Statamic\Query\Scopes\Filter;

class Site extends Filter
{
    public $required = true;

    public function fieldItems()
    {
        return [
            'value' => [
                'display' => __('Site'),
                'type' => 'select',
                'options' => $this->options()->all(),
            ]
        ];
    }

    public function apply($query, $values)
    {
        $query->where('site', $values['value']);
    }

    public function visibleTo($key)
    {
        return $key === 'entries' && Facades\Site::hasMultiple();
    }

    protected function options()
    {
        return Facades\Site::all()->mapWithKeys(function ($site) {
            return [$site->handle() => $site->name()];
        });
    }
}
