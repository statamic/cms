<?php

namespace Statamic\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType as BaseObjectType;

abstract class ObjectType extends BaseObjectType
{
    public function __construct(array $args)
    {
        parent::__construct($this->config($args));
    }

    abstract public function config(array $args): array;
}
