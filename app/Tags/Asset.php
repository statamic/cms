<?php

namespace Statamic\Tags;

use Statamic\API\Helper;
use Statamic\API\Asset as AssetAPI;

class Asset extends Assets
{
    /**
     * Gets a single Asset's data from a value
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
        $value = array_get_colon($this->context, $this->method);
        $value = Helper::ensureArray($value);
        $value = current($value);

        return $this->assets($value);
    }

    /**
     * Gets a single Asset's data from a URL
     *
     * @return mixed
     */
    public function index()
    {
        $asset = AssetAPI::find($this->get('url'));

        return $asset ? $asset->toArray() : $asset;
    }
}
