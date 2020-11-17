<?php

namespace Statamic\Tags;

use Statamic\Facades\Site;

class Sites extends Tags
{
    public function index()
    {
        return collect(Site::all())->map(function($site) {
            return [
                'handle' => $site->handle(),
                'name' => $site->name(),
                'locale' => $site->locale(),
                'url' => $site->url(),
            ];
        })->values()->all();
    }

}
