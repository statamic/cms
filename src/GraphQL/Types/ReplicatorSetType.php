<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;

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
        $fields = $this->fieldtype->fields($this->handle)->toGql();

        return $fields
            ->merge([
                'id' => [
                    'type' => GraphQL::string(),
                ],
                'type' => [
                    'type' => GraphQL::nonNull(GraphQL::string()),
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
