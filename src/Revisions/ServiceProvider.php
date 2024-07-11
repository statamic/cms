<?php

namespace Statamic\Revisions;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Statamic\Contracts\Revisions\RevisionRepository as RevisionRepositoryContract;
use Statamic\Stache\Query\RevisionQueryBuilder;
use Statamic\Stache\Stache;
use Statamic\Statamic;

class ServiceProvider extends LaravelServiceProvider
{
    public function register()
    {
        Statamic::repository(RevisionRepositoryContract::class, RevisionRepository::class);

        $this->app->bind(RevisionQueryBuilder::class, function () {
            return new RevisionQueryBuilder($this->app->make(Stache::class)->store('revisions'));
        });
    }
}
