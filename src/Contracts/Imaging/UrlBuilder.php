<?php

namespace Statamic\Contracts\Imaging;

interface UrlBuilder
{
    /**
     * Build the URL.
     *
     * @param  \Statamic\Contracts\Assets\Asset|string  $item
     * @param  array  $params
     * @return string
     */
    public function build($item, $params);
}
