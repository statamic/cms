<?php

namespace Statamic\Structures;

use Statamic\Support\Str;

class BranchIdGenerator
{
    public function generate()
    {
        return (string) Str::uuid();
    }
}
