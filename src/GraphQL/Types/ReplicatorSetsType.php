<?php

namespace Statamic\GraphQL\Types;

use Rebing\GraphQL\Support\UnionType;
use Statamic\Facades\GraphQL;
use Statamic\Support\Str;

class ReplicatorSetsType extends UnionType
{
    protected $fieldtype;
    protected $types;
    protected $typeMap;

    public function __construct($fieldtype, $name, $types)
    {
        $this->fieldtype = $fieldtype;
        $this->attributes['name'] = $name;

        $this->types = $types->mapWithKeys(function ($item) {
            return [$item['name'] => GraphQL::type($item['name'])];
        })->all();

        $this->typeMap = $types->pluck('name', 'handle')->all();
    }

    public static function buildName($fieldtype)
    {
        return 'Sets_'.Str::studly($fieldtype->field()->handle());
    }

    public function types(): array
    {
        return $this->types;
    }

    public function resolveType($value)
    {
        return GraphQL::type($this->typeMap[$value['type']]);
    }
}
