<?php

namespace Statamic\Tokens;

use Statamic\Support\Str;

class Generator
{
    public function generate()
    {
        return Str::random(40);
    }
}
