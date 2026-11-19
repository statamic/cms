<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Statamic\Forms\Fields\FormFieldTransformer;

class FormGroupFields extends Fieldtype
{
    protected static $handle = 'form_group_fields';

    protected $selectable = false;

    public function preProcess($fields)
    {
        return collect($fields)
            ->map(fn ($field, $i) => array_merge(FormFieldTransformer::toVue($field), ['_id' => $i]))
            ->values()
            ->all();
    }

    public function process($fields)
    {
        return collect($fields)
            ->map(fn ($field) => FormFieldTransformer::fromVue($field))
            ->values()
            ->all();
    }
}
