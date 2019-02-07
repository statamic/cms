<?php

namespace Statamic\Tags;

use Statamic\Tags\Tag;

class Obfuscate extends Tag
{
    public function index()
    {
        return app('html')->obfuscate($this->content);
    }
}
