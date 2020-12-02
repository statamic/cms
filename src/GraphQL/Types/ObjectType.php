<?php

namespace Statamic\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType as BaseObjectType;

abstract class ObjectType extends BaseObjectType
{
    public function __construct(array $args)
    {
        parent::__construct(array_merge([
            'name' => static::name($args),
        ], $this->config($args)));
    }

    abstract public static function name(array $args): string;

    abstract public function config(array $args): array;
}
