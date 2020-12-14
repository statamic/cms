<?php

namespace Statamic\GraphQL\Types;

use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Contracts\Globals\Variables;
use Statamic\Fields\Value;
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
            ? $blueprint->fields()->toGraphQL()
            : collect();

        return $fields
            ->merge((new GlobalSetInterface)->fields())
            ->map(function (array $arr) {
                $arr['resolve'] = $this->resolver();

                return $arr;
            })
            ->all();
    }

    private function resolver()
    {
        return function (Variables $globals, $args, $context, $info) {
            $value = $globals->augmentedValue($info->fieldName);

            if ($value instanceof Value) {
                $value = $value->value();
            }

            return $value;
        };
    }
}
