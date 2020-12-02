<?php

namespace Statamic\Fieldtypes;

use GraphQL\Type\Definition\Type;
use Statamic\Fields\Fieldtype;

class Integer extends Fieldtype
{
    protected $rules = ['integer'];

    public function preProcess($data)
    {
        if ($data === null) {
            return null;
        }

        return (int) $data;
    }

    public function preProcessConfig($data)
    {
        return (int) $data;
    }

    public function process($data)
    {
        if ($data === null || $data === '') {
            return null;
        }

        return (int) $data;
    }

    public function graphQLType(): Type
    {
        return Type::int();
    }
}
