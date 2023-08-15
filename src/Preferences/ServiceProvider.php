<?php

namespace Statamic\Preferences;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Statamic\Facades\Preference;

class ServiceProvider extends LaravelServiceProvider
{
    public function boot()
    {
        Preference::preventMergingChildren('nav');
    }
}
