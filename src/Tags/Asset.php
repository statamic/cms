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
     * @return string
     */
    public function wildcard($method)
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

        if (! $asset = $this->params->get(['url', 'src'])) {
            return null;
        }

        return AssetAPI::find($asset);
    }
}
