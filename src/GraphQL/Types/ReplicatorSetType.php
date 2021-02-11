<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Value;

class ReplicatorSetType extends \Rebing\GraphQL\Support\Type
{
    protected $fieldtype;
    protected $handle;

    public function __construct($fieldtype, $name, $handle)
    {
        $this->fieldtype = $fieldtype;
        $this->handle = $handle;
        $this->attributes['name'] = $name;
    }

    public function fields(): array
    {
        return $this->fieldtype->fields($this->handle)->toGql()
            ->merge([
                'type' => [
                    'type' => GraphQL::nonNull(GraphQL::string()),
                ],
            ])
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
