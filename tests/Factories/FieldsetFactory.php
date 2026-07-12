<?php

namespace Tests\Factories;

use Statamic\Fields\Fieldset;

class FieldsetFactory
{
    protected $contents;

    public function withContents($contents)
    {
        $this->contents = $contents;

        return $this;
    }

    public function withTabs($tabs)
    {
        if (! $this->contents) {
            $this->contents = [];
        }

        $this->contents['tabs'] = $tabs;

        return $this;
    }

    public function withFieldtypes($fieldtypes)
    {
        foreach ($fieldtypes as $name => $fieldtype) {
            $this->fieldtypes[$name] = $fieldtype;
        }

        return $this;
    }

    public function withFields($fields)
    {
        if (! $this->contents) {
            $this->contents = [];
        }

        $this->contents['fields'] = $fields;

        return $this;
    }

    public function create()
    {
        return tap(new Fieldset)
            ->contents($this->contents);
    }
}
