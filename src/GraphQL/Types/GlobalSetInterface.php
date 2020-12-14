<?php

namespace Statamic\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\InterfaceType;
use Statamic\Facades\GlobalSet;

class GlobalSetInterface extends InterfaceType
{
    const NAME = 'GlobalSetInterface';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return [
            'handle' => [
                'type' => Type::nonNull(Type::string()),
            ],
        ];
    }

    public function resolveType($globals)
    {
        return GraphQL::type(GlobalSetType::buildName($globals));
    }

    public static function addTypes()
    {
        GraphQL::addType(self::class);
        GraphQL::addTypes(GlobalSet::all()->mapInto(GlobalSetType::class)->all());
    }
}
