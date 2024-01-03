<?php

namespace Statamic\Http\View\Composers;

use Illuminate\View\View;
use Statamic\Facades\Blink;
use Statamic\Facades\CP\Nav;

class NavComposer
{
    const VIEWS = [
        'statamic::partials.nav-main',
        'statamic::partials.nav-mobile',
    ];

    public function compose(View $view)
    {
        $view->with('nav', Blink::once('nav-composer-navigation', fn () => Nav::build()));
    }
}
