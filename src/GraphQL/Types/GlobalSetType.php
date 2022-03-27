<?php

namespace Statamic\GraphQL\Types;

use Statamic\Contracts\Globals\Variables;
use Statamic\Facades\GraphQL;
use Statamic\Support\Str;

class GlobalSetType extends \Rebing\GraphQL\Support\Type
{
    private $globals;

    public function __construct($globals)
    {
        $this->globals = $globals;
        $this->attributes['name'] = static::buildName($globals);
    }

    public static function buildName($globals): string
    {
        return 'GlobalSet_'.Str::studly($globals->handle());
    }

    public function interfaces(): array
    {
        return [
            GraphQL::type(GlobalSetInterface::NAME),
        ];
    }

    public function fields(): array
    {
        $fields = ($blueprint = $this->globals->blueprint())
            ? $blueprint->fields()->toGql()
            : collect();

        return $fields
            ->merge((new GlobalSetInterface)->fields())
            ->merge(collect(GraphQL::getExtraTypeFields($this->name))->map(function ($closure) {
                return $closure();
            }))
            ->map(function (array $arr) {
                $arr['resolve'] = $arr['resolve'] ?? $this->resolver();

                return $arr;
            })
            ->all();
    }

    private function resolver()
    {
        return function (Variables $globals, $args, $context, $info) {
            if ($info->fieldName === 'handle') {
                return $globals->handle();
            }

            return $globals->resolveGqlValue($info->fieldName);
        };
    }
}
