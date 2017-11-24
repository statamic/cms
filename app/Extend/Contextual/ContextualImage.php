<?php

namespace Statamic\Extend\Contextual;

use Statamic\API\Str;

class ContextualImage extends ContextualResource
{
    public function url($path)
    {
        return parent::url('img/' . $path);
    }

    public function tag($path)
    {
        return '<img src="' . $this->url($path) . '" alt="" />';
    }
}
