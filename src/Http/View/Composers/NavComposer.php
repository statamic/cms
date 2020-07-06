<?php

namespace Statamic\Http\View\Composers;

use Illuminate\View\View;
use Statamic\Facades\CP\Nav;

class NavComposer
{
    const VIEWS = [
        'statamic::partials.nav-main',
        'statamic::partials.nav-mobile',
    ];

    protected static $nav;

    public function compose(View $view)
    {
        $view->with('nav', self::$nav = self::$nav ?? Nav::build());
    }
}
