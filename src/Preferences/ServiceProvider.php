<?php

namespace Statamic\Preferences;

use Facades\Statamic\Preferences\CorePreferences;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Statamic\Facades\Preference;

class ServiceProvider extends LaravelServiceProvider
{
    public function boot()
    {
        Preference::preventMergingChildren('nav');

        CorePreferences::boot();
    }
}
