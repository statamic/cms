<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;
use Statamic\Support\Str;

class BardTextType extends \Rebing\GraphQL\Support\Type
{
    protected $fieldtype;

    public function __construct($fieldtype)
    {
        $this->fieldtype = $fieldtype;

        $this->attributes['name'] = static::buildName($fieldtype);
    }

    public static function buildName($fieldtype)
    {
        return 'BardText_'.Str::studly($fieldtype->field()->handle());
    }

    public function fields(): array
    {
        return [
            'type' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
            'text' => [
                'type' => GraphQL::string(),
            ],
        ];
    }
}
