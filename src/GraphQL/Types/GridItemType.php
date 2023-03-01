<?php

namespace Statamic\GraphQL\Types;

use Rebing\GraphQL\Support\Type;
use Statamic\Facades\GraphQL;

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
        $fields = $this->fieldtype->fields()->toGql();

        return $fields
            ->merge([
                'id' => [
                    'type' => GraphQL::string(),
                ],
            ])
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
