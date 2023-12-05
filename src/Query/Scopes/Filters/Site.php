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
        $site = Facades\Site::get($values['site']);

        return __('Site').': '.__($site->name());
    }

    public function visibleTo($key)
    {
        return $key === 'entries' && Facades\Site::hasMultiple();
    }

    protected function options()
    {
        return Facades\Site::authorized()->mapWithKeys(function ($site) {
            return [$site->handle() => __($site->name())];
        });
    }
}
