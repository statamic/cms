<?php

namespace Statamic\Http\ViewComposers;

use Illuminate\Contracts\View\View;

class NavigationComposer
{
    public function compose(View $view)
    {
        app('Statamic\CP\Navigation\NavFactory')->build();

        $nav = app('Statamic\CP\Navigation\Nav');

        event('cp.nav.created', $nav);

        $nav->trim();

        $view->with('nav', $nav);
    }
}
