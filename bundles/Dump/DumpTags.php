<?php

namespace Statamic\Addons\Dump;

use Statamic\Extend\Tags;

class DumpTags extends Tags
{
    public function index()
    {
        dd($this->context);
    }
}
