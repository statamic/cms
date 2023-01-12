<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\Site as SiteFacade;
use Statamic\Facades\User;
use Statamic\Sites\Site;

class Sites extends Relationship
{
    protected $indexComponent = 'text';

    public function toItemArray($id)
    {
        if ($site = SiteFacade::get($id)) {
            return [
                'id' => $id,
                'title' => $site->name(),
            ];
        }

        return $this->invalidItemArray($id);
    }

    public function getIndexItems($request)
    {
        return SiteFacade::all()->filter(function (Site $site) {
            return User::current()->can('view', $site);
        })->sortBy('name')->map(function ($site) {
            return [
                'id' => $site->handle(),
                'title' => $site->name(),
            ];
        })->values();
    }

    public function augmentValue($value)
    {
        return SiteFacade::get($value);
    }

    public function preProcessIndex($data)
    {
        if (! $items = $this->augment($data)) {
            return [];
        }

        if ($this->config('max_items') === 1) {
            $items = collect([$items]);
        }

        return $items->map->name()->join(', ');
    }
}
