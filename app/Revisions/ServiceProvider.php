<?php

namespace Statamic\Revisions;

use Statamic\Statamic;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Statamic\Contracts\Revisions\RevisionRepository as RevisionRepositoryContract;

class ServiceProvider extends LaravelServiceProvider
{
    public function boot()
    {
        Statamic::provideToScript(['revisions' => [
            'enabled' => config('statamic.revisions.enabled'),
        ]]);
    }

    public function register()
    {
        $this->app->bind(RevisionRepositoryContract::class, RevisionRepository::class);
    }
}
