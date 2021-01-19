<?php

namespace Statamic\GraphQL\Types;

use Statamic\Contracts\Entries\Collection;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades\GraphQL;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Value;
use Statamic\Support\Str;

class EntryType extends \Rebing\GraphQL\Support\Type
{
    private $collection;
    private $blueprint;

    public function __construct($collection, $blueprint)
    {
        $this->collection = $collection;
        $this->blueprint = $blueprint;
        $this->attributes['name'] = static::buildName($collection, $blueprint);
    }

    public static function buildName(Collection $collection, Blueprint $blueprint): string
    {
        return 'Entry_'.Str::studly($collection->handle()).'_'.Str::studly($blueprint->handle());
    }

    public function interfaces(): array
    {
        return [
            GraphQL::type(EntryInterface::NAME),
        ];
    }

    public function fields(): array
    {
        return $this->blueprint->fields()->toGraphQL()
            ->merge((new EntryInterface)->fields())
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
        return function (EntryContract $entry, $args, $context, $info) {
            $value = $entry->augmentedValue($info->fieldName);

            if ($value instanceof Value) {
                $value = $value->value();
            }

            return $value;
        };
    }
}
