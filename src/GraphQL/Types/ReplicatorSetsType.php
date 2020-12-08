<?php

namespace Statamic\GraphQL\Types;

use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\UnionType;
use Statamic\Support\Str;

class ReplicatorSetsType extends UnionType
{
    protected $fieldtype;
    protected $types;

    public function __construct($fieldtype, $types)
    {
        $this->fieldtype = $fieldtype;
        $this->types = $types;

        $this->attributes['name'] = static::buildName($fieldtype);
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
        return GraphQL::type(ReplicatorSetType::buildName($this->fieldtype, $value['type']));
    }
}
