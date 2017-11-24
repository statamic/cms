<?php

namespace Statamic\Extend\Contextual;

use Statamic\API\Str;

class ContextualCss extends ContextualResource
{
    public function url($path)
    {
        return parent::url('css/' . Str::ensureRight($path, '.css'));
    }

    public function inline($str)
    {
        return '<style>' . $str . '</style>';
    }

    public function tag($path)
    {
        return '<link rel="stylesheet" href="' . $this->url($path) . '" />';
    }
}
