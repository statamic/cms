<?php

namespace Statamic\GraphQL\Types;

use Rebing\GraphQL\Support\UnionType;
use Statamic\Facades\GraphQL;
use Statamic\Support\Str;

class EntriesSelectType extends UnionType
{
    public $name;
    public $typeNames;

    public function __construct(string $name, array $typeNames)
    {
        $this->attributes['name'] = $name;
        $this->name = $name;
        $this->typeNames = $typeNames;
    }

    public static function buildName(array $collections): string
    {
        sort($collections);

        return 'Entries_'.implode('_', array_map(fn ($c) => Str::studly($c), $collections));
    }

    public function types(): array
    {
        return array_map(fn ($name) => GraphQL::type($name), $this->typeNames);
    }

    public function resolveType($entry)
    {
        return GraphQL::type(EntryType::buildName($entry->collection(), $entry->blueprint()));
    }
}
