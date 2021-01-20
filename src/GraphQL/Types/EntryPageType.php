<?php

namespace Statamic\GraphQL\Types;

use Statamic\Contracts\Entries\Collection;
use Statamic\Facades\GraphQL;
use Statamic\Fields\Blueprint;
use Statamic\Support\Str;

class EntryPageType extends EntryType
{
    public static function buildName(Collection $collection, Blueprint $blueprint): string
    {
        return 'EntryPage_'.Str::studly($collection->handle()).'_'.Str::studly($blueprint->handle());
    }

    public function interfaces(): array
    {
        return [
            GraphQL::type(PageInterface::NAME),
        ];
    }

    public function fields(): array
    {
        return $this->blueprint->fields()->toGraphQL()
            ->merge((new PageInterface)->fields())
            ->merge(collect(GraphQL::getExtraTypeFields(EntryInterface::NAME))->map(function ($closure) {
                return $closure();
            }))
            ->merge(collect(GraphQL::getExtraTypeFields($this->name))->map(function ($closure) {
                return $closure();
            }))
            ->map(function (array $arr) {
                $arr['resolve'] = $arr['resolve'] ?? $this->resolver();

                return $arr;
            })
            ->all();
    }
}
