<?php

namespace Statamic\StaticCaching\NoCache;

class ViewView
{
    private $name;
    private $data;

    public function __construct($name, $data)
    {
        $this->name = $name;
        $this->data = $data;
    }

    public function render()
    {
        return view($this->name, $this->data)->render();
    }
}
