<?php

namespace Statamic\Http\View\Composers;

use Illuminate\View\View;
use Statamic\CP\Breadcrumbs\Breadcrumbs;
use Statamic\Facades\Blink;
use Statamic\Facades\CP\Nav;

class NavComposer
{
    const VIEWS = [
        'statamic::partials.nav-main',
        'statamic::partials.nav-mobile',
        'statamic::partials.global-header',
    ];

    public function compose(View $view)
    {
        $view->with('nav', Blink::once('nav-composer-navigation', fn () => Nav::build()));
        $view->with('breadcrumbs', Blink::once('nav-composer-breadcrumbs', fn () => Breadcrumbs::build()));
    }
}
