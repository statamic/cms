<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;

class Integer extends Fieldtype
{
    protected $rules = ['integer'];
    protected $selectableInForms = true;

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

    public function toGqlType()
    {
        return GraphQL::int();
    }
}
