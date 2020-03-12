<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Routing\ResolveRedirect;

class Redirect extends Fieldtype
{
    public function augment($value)
    {
        return (new ResolveRedirect)($value, $this->field->parent());
    }

    public function preload()
    {
        $entryFieldtype = (new Field('entry', [
            'type' => 'entries',
            'max_items' => 1,
            'create' => false,
        ]))->fieldtype();

        return [
            'entry' => [
                'config' => $entryFieldtype->config(),
                'meta' => $entryFieldtype->preload(),
            ]
        ];
    }
}
