<?php

namespace Statamic\Tags;

use Statamic\Facades\Asset as AssetAPI;
use Statamic\Support\Arr;

class Asset extends Assets
{
    /**
     * Gets a single Asset's data from a value.
     *
     * Usage:
     * {{ asset:[variable] }}
     *   {{ url }}, etc
     * {{ /asset:[variable] }}
     *
     * Note:
     * If the variable contains an array of IDs, we'll use only the first.
     * To iterate over multiple assets, use {{ assets:[variable }} instead.
     *
     * @param $method
     * @param $arguments
     * @return string
     */
    public function __call($method, $arguments)
    {
        $value = Arr::get($this->context, $this->method);
        $value = (array) $value;
        $value = current($value);

        return $this->assets($value);
    }

    /**
     * Gets a single Asset's data from a URL.
     *
     * @return mixed
     */
    public function index()
    {
        if (! $this->params->hasAny(['url', 'src'])) {
            return $this->context->value('asset');
        }

        return AssetAPI::find($this->params->get(['url', 'src']));
    }
}
