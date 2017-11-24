<?php

namespace Statamic\View;

class Store
{
    private $sections;

    public function __construct()
    {
        $this->sections = collect();
    }

    public function sections()
    {
        return $this->sections;
    }
}
