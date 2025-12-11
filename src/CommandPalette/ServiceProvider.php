<?php

namespace Statamic\CommandPalette;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    public function boot()
    {
        Event::subscribe(Subscriber::class);
    }
}
