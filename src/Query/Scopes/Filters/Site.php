<?php

namespace Statamic\Query\Scopes\Filters;

use Illuminate\Support\Arr;
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
        if ($key === 'entries' && $this->availableSites()->isNotEmpty()) {
            return true;
        }

        return $key === 'entries-fieldtype' && $this->context['showSiteFilter'] && $this->availableSites()->isNotEmpty();
    }

    protected function options()
    {
        return $this->availableSites()
            ->mapWithKeys(fn ($site) => [$site->handle() => __($site->name())]);
    }

    protected function availableSites()
    {
        if (! Facades\Site::hasMultiple()) {
            return collect();
        }

        // Get the configured sites of a single collection when on the entries index view.
        if ($collection = Arr::get($this->context, 'collection')) {
            $configuredSites = Collection::find($collection)->sites();
        }

        // Get the configured sites of multiple collections when in the entries fieldtype.
        if ($collections = Arr::get($this->context, 'collections')) {
            $configuredSites = collect($collections)->flatMap(fn ($collection) => Collection::find($collection)->sites());
        }

        if (! isset($configuredSites)) {
            return Facades\Site::authorized();
        }

        return Facades\Site::authorized()
            ->filter(fn ($site) => $configuredSites->contains($site->handle()));
    }
}
