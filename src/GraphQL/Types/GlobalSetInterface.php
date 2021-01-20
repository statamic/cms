<?php

namespace Statamic\GraphQL\Types;

use Rebing\GraphQL\Support\InterfaceType;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\GraphQL;

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
                'type' => GraphQL::nonNull(GraphQL::string()),
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

        GraphQL::addTypes(GlobalSet::all()->each(function ($globals) {
            optional($globals->blueprint())->addGqlTypes();
        })->mapInto(GlobalSetType::class)->all());
    }
}
