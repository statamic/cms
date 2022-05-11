<?php

namespace Statamic\Providers;

use Illuminate\Support\ServiceProvider;
use Statamic\Fieldtypes\Bard\Augmentor;
use Statamic\Fieldtypes\Bard\Marks\Small;

class BardServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Augmentor::addMark(new Small);
    }
}
