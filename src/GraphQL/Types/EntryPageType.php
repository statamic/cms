<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;
use Statamic\Contracts\Entries\Collection;
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
}
