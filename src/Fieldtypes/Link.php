<?php

namespace Statamic\Fieldtypes;

use Statamic\Contracts\Entries\Collection;
use Statamic\Contracts\Entries\Entry;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Routing\ResolveRedirect;
use Statamic\Support\Str;

class Link extends Fieldtype
{
    public function augment($value)
    {
        $redirect = (new ResolveRedirect)($value, $this->field->parent());

        return $redirect === 404 ? null : $redirect;
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
            'showFirstChildOption' => $this->showFirstChildOption(),
            'entry' => [
                'config' => $entryFieldtype->config(),
                'meta' => $entryFieldtype->preload(),
            ],
        ];
    }

    protected function showFirstChildOption()
    {
        $parent = $this->field()->parent();

        if ($parent instanceof Entry) {
            $collection = $parent->collection();
        } elseif ($parent instanceof Collection) {
            $collection = $parent;
        } else {
            return false;
        }

        return $collection->hasStructure() && $collection->structure()->maxDepth() !== 1;
    }
}
