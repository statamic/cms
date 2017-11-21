<?php

namespace Statamic\Contracts\Data;

use Illuminate\Contracts\Support\Arrayable;

interface LocalizedData extends Arrayable
{
    /**
     * Get the underlying data object
     *
     * @return \Statamic\Contracts\Data\Data
     */
    public function get();

    /**
     * Pass along any method calls to the underlying data object
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args);
}