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
        return $this->fieldtype->fields($this->handle)->toGraphQL()
            ->merge([
                'type' => [
                    'type' => GraphQL::nonNull(GraphQL::string()),
                ],
            ])
            ->all();
    }
}
