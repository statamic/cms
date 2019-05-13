<?php

namespace Statamic\Revisions;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Statamic\Contracts\Revisions\RevisionRepository as RevisionRepositoryContract;

class ServiceProvider extends LaravelServiceProvider
{
    public function register()
    {
        $this->app->bind(RevisionRepositoryContract::class, RevisionRepository::class);
    }
}
