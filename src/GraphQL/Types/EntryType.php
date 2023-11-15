<?php

namespace Statamic\GraphQL\Types;

use Statamic\Contracts\Entries\Collection;
use Statamic\Contracts\GraphQL\ResolvesValues;
use Statamic\Facades\GraphQL;
use Statamic\Fields\Blueprint;
use Statamic\Support\Str;

class EntryType extends \Rebing\GraphQL\Support\Type
{
    protected $collection;
    protected $blueprint;

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
        return $this->blueprint->fields()->toGql()
            ->merge((new EntryInterface)->fields())
            ->merge(collect(GraphQL::getExtraTypeFields($this->name))->map(function ($closure) {
                return $closure();
            }))
            ->map(function ($arr) {
                if (is_array($arr)) {
                    $arr['resolve'] = $arr['resolve'] ?? $this->resolver();
                }

                return $arr;
            })
            ->all();
    }

    protected function resolver()
    {
        return function (ResolvesValues $entry, $args, $context, $info) {
            return $entry->resolveGqlValue($info->fieldName);
        };
    }
}
