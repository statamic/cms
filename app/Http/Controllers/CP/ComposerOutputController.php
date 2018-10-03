<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Support\Facades\Cache;

class ComposerOutputController
{
    /**
     * Get composer output from cache for realtime output in browser.
     *
     * @return mixed
     */
    public function check()
    {
        $cache = Cache::get('composer');

        if ($completed = data_get(Cache::get('composer'), 'completed', true)) {
            Cache::forget('composer');
        }

        return $cache;
    }
}
