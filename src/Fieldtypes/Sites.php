<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\Site;

class Sites extends Relationship
{
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
        return Site::all()->map(function ($site) {
            return [
                'id' => $site->handle(),
                'title' => $site->name(),
            ];
        })->values();
    }
}
