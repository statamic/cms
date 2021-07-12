<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;

class Toggle extends Fieldtype
{
    protected $defaultValue = false;

    protected function defaultConfigFieldItem(): ?array
    {
        return array_replace(parent::defaultConfigFieldItem(), ['type' => 'toggle']);
    }

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

    public function toGqlType()
    {
        return GraphQL::boolean();
    }
}
