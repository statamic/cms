<?php

namespace Statamic\GraphQL;

use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\GraphQL\Types\CollectionType;
use Statamic\GraphQL\Types\EntryInterface;

class TypeRegistrar
{
    private $registered = false;

    public function register()
    {
        if ($this->registered) {
            return;
        }

        GraphQL::addType(CollectionType::class);
        EntryInterface::addTypes();

        $this->registered = true;
    }
}
