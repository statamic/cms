<?php

namespace Statamic\Query\Scopes\Filters;

use Statamic\Facades;
use Statamic\Facades\Collection;
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
        return in_array($key, ['entries', 'entries-fieldtype'])
            && Facades\Site::authorized()->count() > 1;
    }

    protected function options()
    {
        $configuredSites = Collection::find($this->context['collection'])->sites();

        return Facades\Site::authorized()
            ->filter(fn ($site) => $configuredSites->contains($site->handle()))
            ->mapWithKeys(fn ($site) => [$site->handle() => __($site->name())]);
    }
}
