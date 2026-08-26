<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\Site;
use Statamic\Facades\User;

class Sites extends Relationship
{
    protected $indexComponent = 'sites';

    protected function authorizeItemData($id): bool
    {
        return $this->authorizeViewable(Site::get($id));
    }

    public function toItemArray($id)
    {
        if ($site = Site::get($id)) {
            return [
                'id' => $id,
                'title' => $site->name(),
                'group' => $site->group(),
                'group_handle' => $site->groupHandle(),
            ];
        }

        return $this->invalidItemArray($id);
    }

    public function getIndexItems($request)
    {
        $sites = Site::all()
            ->filter(fn ($site) => User::current()->can('view', $site));

        // Preserve sites.yaml order when groups exist; otherwise keep the old A–Z sort.
        if (! $sites->contains(fn ($site) => $site->group())) {
            $sites = $sites->sortBy->name();
        }

        return $sites
            ->map(function ($site) {
                return [
                    'id' => $site->handle(),
                    'title' => $site->name(),
                    'group' => $site->group(),
                    'group_handle' => $site->groupHandle(),
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

        return $items
            ->filter()
            ->map(fn ($site) => [
                'title' => $site->name(),
                'group' => $site->group(),
                'group_handle' => $site->groupHandle(),
            ])
            ->values()
            ->all();
    }
}
