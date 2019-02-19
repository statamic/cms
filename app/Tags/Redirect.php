<?php

namespace Statamic\Tags;

use Statamic\Tags\Tags;
use Statamic\Exceptions\RedirectException;

class Redirect extends Tags
{
    public function index()
    {
        $e = new RedirectException;

        $e->setUrl($this->get(['to', 'url']));
        $e->setCode($this->get('response', 302));

        throw $e;
    }
}
