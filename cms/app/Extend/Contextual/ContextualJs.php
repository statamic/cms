<?php

namespace Statamic\Extend\Contextual;

use Statamic\API\Str;

class ContextualJs extends ContextualResource
{
    public function url($path)
    {
        return parent::url('js/' . Str::ensureRight($path, '.js'));
    }

    public function inline($str)
    {
        return '<script type="text/javascript">' . $str . '</script>';
    }

    public function tag($path)
    {
        return '<script src="' . $this->url($path) . '"></script>';
    }
}
