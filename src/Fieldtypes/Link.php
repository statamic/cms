<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Routing\ResolveRedirect;
use Statamic\Support\Str;

class Link extends Fieldtype
{
    public function augment($value)
    {
        return (new ResolveRedirect)($value, $this->field->parent());
    }

    public function preload()
    {
        $value = $this->field->value();

        $entryField = (new Field('entry', [
            'type' => 'entries',
            'max_items' => 1,
            'create' => false,
        ]));

        if (Str::startsWith($value, 'entry::')) {
            $entryField->setValue(Str::after($value, 'entry::'));
        }

        $entryFieldtype = $entryField->fieldtype();

        return [
            'entry' => [
                'config' => $entryFieldtype->config(),
                'meta' => $entryFieldtype->preload(),
            ]
        ];
    }
}
