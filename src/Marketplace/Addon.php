<?php

namespace Statamic\Marketplace;

class Addon
{
    protected $addon;

    public function __construct($addon)
    {
        $this->addon = $addon;
    }

    public function name()
    {
        return $this->addon->name();
    }

    public function package()
    {
        return $this->addon->package();
    }

    public function changelog()
    {
        return $this->addon->changelog();
    }
}
