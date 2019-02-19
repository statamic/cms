<?php

namespace Statamic\Tags;

use Statamic\Tags\Tags;

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
