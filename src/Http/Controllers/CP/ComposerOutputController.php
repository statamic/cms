<?php

namespace Statamic\Http\Controllers\CP;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\Controller;

class ComposerOutputController extends Controller
{
    /**
     * Get composer output from cache for realtime output in browser.
     *
     * @return mixed
     */
    public function check(Request $request)
    {
        return Composer::colorized()->cachedOutput($request->package);
    }
}
