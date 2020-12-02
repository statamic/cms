<?php

namespace Statamic\GraphQL\Types;

use Facades\Statamic\GraphQL\TypeRepository;
use GraphQL\Type\Definition\InterfaceType as BaseInterfaceType;

abstract class InterfaceType extends BaseInterfaceType
{
    public static function registerTypes()
    {
        foreach (static::types() as $name => $type) {
            TypeRepository::register($name, $type);
        }
    }

    public static function types(): array
    {
        return [];
    }
}
