<?php

namespace Statamic\Query\Scopes\Filters;

use Statamic\Facades;
use Statamic\Query\Scopes\Filter;

class Site extends Filter
{
    public function fieldItems()
    {
        return [
            'site' => [
                'display' => __('Site'),
                'type' => 'select',
                'options' => $this->options()->all(),
            ]
        ];
    }

    public function apply($query, $values)
    {
        $query->where('site', $values['site']);
    }

    public function badge($values)
    {
        return __('in') . ' ' . strtolower($values['site']) . ' ' . __('site');
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
