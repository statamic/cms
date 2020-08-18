<?php

namespace Statamic\Tags;

class Mix extends Tags
{
    public function index()
    {
        return mix(
            $this->params->get(['src', 'path']),
            $this->params->get(['from', 'in'], '')
        );
    }
}
