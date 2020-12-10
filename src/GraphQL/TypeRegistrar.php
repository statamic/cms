<?php

namespace Statamic\GraphQL;

use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\GraphQL\Types\AssetInterface;
use Statamic\GraphQL\Types\CollectionType;
use Statamic\GraphQL\Types\EntryInterface;
use Statamic\GraphQL\Types\JsonArgument;

class TypeRegistrar
{
    private $registered = false;

    public function register()
    {
        if ($this->registered) {
            return;
        }

        GraphQL::addType(JsonArgument::class);
        GraphQL::addType(CollectionType::class);
        EntryInterface::addTypes();
        AssetInterface::addTypes();

        $this->registered = true;
    }
}
