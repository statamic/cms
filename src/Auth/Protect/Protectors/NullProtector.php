<?php

namespace Statamic\Auth\Protect\Protectors;

class NullProtector extends Protector
{
    public function protect()
    {
        // Do nothing.
    }
}
