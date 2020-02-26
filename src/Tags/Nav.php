<?php

namespace Statamic\Tags;

class Nav extends Structure
{
    public function index()
    {
        return $this->structure($this->get('from', 'collection::pages'));
    }
}
