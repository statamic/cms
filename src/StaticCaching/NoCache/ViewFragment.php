<?php

namespace Statamic\StaticCaching\NoCache;

class ViewFragment implements Fragment
{
    private $name;
    private $data;

    public function __construct($name, $data)
    {
        $this->name = $name;
        $this->data = $data;
    }

    public function render(): string
    {
        return view($this->name, $this->data)->render();
    }
}
