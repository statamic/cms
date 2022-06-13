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
        $fields = [
            'handle' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
            'title' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
            'site' => [
                'type' => GraphQL::nonNull(GraphQL::type(SiteType::NAME)),
                'resolve' => function ($globals) {
                    return $globals->site();
                },
            ],
        ];

        foreach (GraphQL::getExtraTypeFields(static::NAME) as $field => $closure) {
            $fields[$field] = $closure();
        }

        return $fields;
    }

    public function resolveType($globals)
    {
        return GraphQL::type(GlobalSetType::buildName($globals));
    }

    public static function addTypes()
    {
        GraphQL::addTypes(GlobalSet::all()->each(function ($globals) {
            optional($globals->blueprint())->addGqlTypes();
        })->mapInto(GlobalSetType::class)->all());
    }
}
