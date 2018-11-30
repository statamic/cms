<?php

namespace Statamic\Http\Controllers\CP;

class StartPageController extends CpController
{
    public function __invoke()
    {
        session()->reflash();

        // TODO: Make this configurable.
        $url = route('statamic.cp.dashboard');

        return redirect($url);
    }
}
