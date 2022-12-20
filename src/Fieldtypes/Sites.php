<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\Site;

class Sites extends Relationship
{
    protected $indexComponent = 'text';

    public function toItemArray($id)
    {
        if ($site = Site::get($id)) {
            return [
                'id' => $id,
                'title' => $site->name(),
            ];
        }

        return $this->invalidItemArray($id);
    }

    public function getIndexItems($request)
    {
        return Site::all()->sortBy('name')->map(function ($site) {
            return [
                'id' => $site->handle(),
                'title' => $site->name(),
            ];
        })->values();
    }

    public function augmentValue($value)
    {
        return Site::get($value);
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
