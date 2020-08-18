<?php

namespace Statamic\Query\Scopes\Filters;

use Statamic\Facades;
use Statamic\Query\Scopes\Filter;

class Site extends Filter
{
    protected $pinned = true;

    public static function title()
    {
        return __('Site');
    }

    public function fieldItems()
    {
        return [
            'site' => [
                'display' => __('Site'),
                'type' => 'radio',
                'options' => $this->options()->all(),
            ],
        ];
    }

    public function autoApply()
    {
        return [
            'site' => Facades\Site::selected()->handle(),
        ];
    }

    public function apply($query, $values)
    {
        $query->where('site', $values['site']);
    }

    public function badge($values)
    {
        return __('Site').': '.strtolower($values['site']);
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
