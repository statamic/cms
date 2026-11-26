<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class FormHeading extends Fieldtype
{
    protected $selectable = false;

    public function extraRenderableFieldData(): array
    {
        return [
            'heading' => $this->config('display'),
            'subheading' => $this->config('subheading'),
        ];
    }
}
