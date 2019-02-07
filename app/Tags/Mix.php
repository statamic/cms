<?php

namespace Statamic\Tags;

use Statamic\Tags\Tag;

class Mix extends Tag
{
    public function index()
    {
        return mix(
            $this->get(['src', 'path']),
            $this->get(['from', 'in'], '')
        );
    }
}
