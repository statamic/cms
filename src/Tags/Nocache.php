<?php

namespace Statamic\Tags;

use Illuminate\Support\Facades\Cache;

class Nocache extends Tags
{
    public function index()
    {
        // if (! config('statamic.static_caching.strategy')) {
        //     return [];
        // }

        $context = $this->context->except('__env', 'app')->all();

        $context['__content'] = $this->content;

        $serialized = serialize($context);

        $key = md5($serialized);

        Cache::forever('nocache-tag-'.$key, $serialized);

        return sprintf('__STATIC_NOCACHE_%s__', $key);
    }
}
