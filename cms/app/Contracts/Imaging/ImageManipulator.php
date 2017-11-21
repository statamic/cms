<?php

namespace Statamic\Contracts\Imaging;

use Statamic\Contracts\Assets\Asset;

interface ImageManipulator
{
    /**
     * Build the URL and generate the image.
     *
     * @return mixed
     */
    public function build();

    /**
     * Set the item to be manipulated
     *
     * @param Asset|string $item  The item. Can be an asset, an asset ID, a URL, or path.
     * @return mixed
     */
    public function item($item);

    /**
     * Set the parameters
     *
     * @param array $params
     * @return $this
     */
    public function params($params);

    /**
     * Set a parameter
     *
     * @param string $param
     * @param mixed  $value
     */
    public function setParam($param, $value);

    /**
     * Get all the parameters
     *
     * @return array
     */
    public function getParams();
}
