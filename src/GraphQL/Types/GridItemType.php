<?php

namespace Statamic\GraphQL\Types;

use Rebing\GraphQL\Support\Type;
use Statamic\Fields\Value;

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
        return $this->fieldtype->fields()->toGql()
            ->map(function ($field) {
                $field['resolve'] = function ($row, $args, $context, $info) {
                    $value = $row[$info->fieldName];

                    return $value instanceof Value ? $value->value() : $value;
                };

                return $field;
            })
            ->all();
    }
}
