<?php

namespace Statamic\GraphQL\Types;

use Rebing\GraphQL\Support\Type;

class GridItemType extends Type
{
    protected $fieldtype;

    public function __construct($fieldtype, $name)
    {
        $this->fieldtype = $fieldtype;
        $this->attributes['name'] = $name;
    }

    public function fields(): array
    {
        return $this->fieldtype->fields()->toGraphQL()->all();
    }
}
