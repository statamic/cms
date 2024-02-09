<?php

namespace Statamic\GraphQL\Types;

use Rebing\GraphQL\Support\Type;

class GroupType extends Type
{
    protected $fieldtype;

    public function __construct($fieldtype, $name)
    {
        $this->fieldtype = $fieldtype;
        $this->attributes['name'] = $name;
    }

    public function fields(): array
    {
        $fields = $this->fieldtype->fields()->toGql();

        return $fields
            ->map(function ($field) use ($fields) {
                $field['resolve'] = function ($row, $args, $context, $info) use ($fields) {
                    return ($resolver = $fields[$info->fieldName]['resolve'] ?? null)
                         ? $resolver($row, $args, $context, $info)
                         : $row[$info->fieldName];
                };

                return $field;
            })
            ->all();
    }
}
