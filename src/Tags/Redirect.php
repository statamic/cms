<?php

namespace Statamic\Tags;

use Statamic\Tags\Tags;

class Redirect extends Tags
{
    public function index()
    {
        abort(redirect($this->get(['to', 'url']), $this->get('response', 302)));
    }
}
