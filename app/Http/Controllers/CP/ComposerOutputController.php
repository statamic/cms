<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Support\Facades\Cache;
use Statamic\Http\Controllers\Controller;

class ComposerOutputController extends Controller
{
    /**
     * Get composer output from cache for realtime output in browser.
     *
     * @return mixed
     */
    public function check()
    {
        return Cache::get('composer') ?? ['output' => false];
    }
}
