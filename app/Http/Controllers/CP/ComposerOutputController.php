<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Statamic\Http\Controllers\Controller;
use Facades\Statamic\Console\Processes\Composer;

class ComposerOutputController extends Controller
{
    /**
     * Get composer output from cache for realtime output in browser.
     *
     * @return mixed
     */
    public function check(Request $request)
    {
        return Composer::cachedOutput($request->package);
    }
}
