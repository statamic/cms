<?php

namespace Statamic\Preferences;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Statamic\Facades\Preference;
use Facades\Statamic\Preferences\CorePreferences;

class ServiceProvider extends LaravelServiceProvider
{
    public function boot()
    {
        Preference::preventMergingChildren('nav');

        CorePreferences::boot();
    }
}
