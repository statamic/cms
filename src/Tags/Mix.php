<?php

namespace Statamic\Tags;

class Mix extends Tags
{
    public function index()
    {
        return mix(
            $this->get(['src', 'path']),
            $this->get(['from', 'in'], '')
        );
    }
}
