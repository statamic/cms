<?php

namespace Statamic\Tags;

use Statamic\Tags\Tags;

class Obfuscate extends Tags
{
    public function index()
    {
        return app('html')->obfuscate($this->content);
    }
}
