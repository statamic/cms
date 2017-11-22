<?php

namespace Statamic\Addons\Obfuscate;

use Statamic\Extend\Tags;

class ObfuscateTags extends Tags
{
    public function index()
    {
        return app('html')->obfuscate($this->content);
    }
}
