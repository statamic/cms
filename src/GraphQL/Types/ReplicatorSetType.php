<?php

namespace Statamic\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Statamic\Support\Str;

class ReplicatorSetType extends \Rebing\GraphQL\Support\Type
{
    protected $fieldtype;
    protected $handle;

    public function __construct($fieldtype, $handle)
    {
        $this->fieldtype = $fieldtype;
        $this->handle = $handle;

        $this->attributes['name'] = static::buildName($fieldtype, $handle);
    }

    public static function buildName($fieldtype, $set)
    {
        return 'Set_'.Str::studly($fieldtype->field()->handle()).'_'.Str::studly($set);
    }

    public function fields(): array
    {
        return $this->fieldtype->fields($this->handle)->toGraphQL()
            ->merge([
                'type' => [
                    'type' => Type::nonNull(Type::string()),
                ],
            ])
            ->all();
    }
}
