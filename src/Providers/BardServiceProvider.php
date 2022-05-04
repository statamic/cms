<?php

namespace Statamic\Providers;

use Illuminate\Support\ServiceProvider;
use Statamic\Fieldtypes\Bard\Augmentor;
use Statamic\Fieldtypes\Bard\Deaugmentor;
use Statamic\Fieldtypes\Bard\Marks\Small;
use Statamic\Fieldtypes\Bard\Marks\SmallHtml;

class BardServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Augmentor::addMark(Small::class);
        Deaugmentor::addMark(SmallHtml::class);
    }
}
