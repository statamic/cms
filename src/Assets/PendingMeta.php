<?php

namespace Statamic\Assets;

class PendingMeta
{
    protected $key;

    public function __construct($key)
    {
        $this->key = $key;
    }
}
