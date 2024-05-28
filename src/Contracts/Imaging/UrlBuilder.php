<?php

namespace Statamic\Contracts\Imaging;

/**
 * @deprecated New image manipulation methods have been introduced.
 * @see Manipulator
 */
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
