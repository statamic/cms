<?php

namespace Statamic\Contracts\Data\Content;

interface OrderParser
{
    /**
     * Get the page order
     *
     * @param string $path
     * @return mixed
     */
    public function getPageOrder($path);

    /**
     * Get the entry order
     *
     * @param string $path
     * @return mixed
     */
    public function getEntryOrder($path);
}
