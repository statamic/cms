<?php

namespace Statamic\Tags;

use Statamic\Facades\Data;
use Statamic\Facades\Site;

class Link extends Path
{
    public function wildcard($method)
    {
        if ($data = Data::find($method)) {
            $data = $data->in($this->params->get('in', Site::current()->handle()));

            return $this->params->bool('absolute', false) ? $data->absoluteUrl() : $data->url();
        }
    }
}
