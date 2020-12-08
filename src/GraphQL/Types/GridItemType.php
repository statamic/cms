<?php

namespace Statamic\GraphQL\Types;

use Rebing\GraphQL\Support\Type;
use Statamic\Support\Str;

class GridItemType extends Type
{
    protected $fieldtype;

    public function __construct($fieldtype)
    {
        $this->fieldtype = $fieldtype;
        $this->attributes['name'] = 'GridItem_'.Str::studly($fieldtype->field()->handle());
    }

    public function fields(): array
    {
        return $this->fieldtype->fields()->toGraphQL()->all();
    }
}
