<?php

namespace Statamic\GraphQL;

use Statamic\GraphQL\Types\EntryInterface;

class TypeRegistrar
{
    private $registered = false;

    public function register()
    {
        if ($this->registered) {
            return;
        }

        EntryInterface::addTypes();

        $this->registered = true;
    }
}
