<?php

namespace Statamic\Auth\Protect\Protectors;

class Fallback extends Protector
{
    public function protect()
    {
        abort(403);
    }
}
