<?php

namespace Statamic\Fieldtypes;

use GraphQL\Type\Definition\Type;
use Statamic\Fields\Fieldtype;

class Toggle extends Fieldtype
{
    protected $defaultValue = false;

    public function preProcess($data)
    {
        return (bool) $data;
    }

    public function process($data)
    {
        return (bool) $data;
    }

    public function augment($data)
    {
        return (bool) $data;
    }

    public function toGqlType(): Type
    {
        return Type::boolean();
    }
}
