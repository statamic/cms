<?php

namespace Statamic\Tags;

use Statamic\Tags\Tag;
use Statamic\Exceptions\RedirectException;

class Redirect extends Tag
{
    public function index()
    {
        $e = new RedirectException;

        $e->setUrl($this->get(['to', 'url']));
        $e->setCode($this->get('response', 302));

        throw $e;
    }
}
