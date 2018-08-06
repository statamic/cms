<?php

namespace Statamic\Stache\Stores;

class Store
{
    protected $directory;

    public function directory($directory = null)
    {
        if ($directory === null) {
            return $this->directory;
        }

        $this->directory = str_finish($directory, '/');

        return $this;
    }

    public function getItemsFromCache($cache)
    {
        return $cache;
    }
}
