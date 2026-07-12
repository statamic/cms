<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\Facades\Preference;

class StartPageController extends CpController
{
    public function __invoke()
    {
        session()->reflash();

        $url = config('statamic.cp.route').'/'.Preference::get('start_page', config('statamic.cp.start_page'));

        return redirect($url);
    }
}
