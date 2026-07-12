<?php

namespace Statamic\Console\Commands;

use Illuminate\Http\Request;
use Statamic\StaticCaching\Cacher;

class StaticWarmUncachedJob extends StaticWarmJob
{
    public function handle()
    {
        $cacher = app(Cacher::class);

        if ($cacher->hasCachedPage(Request::create($this->request->getUri()))) {
            return;
        }

        parent::handle();
    }
}
