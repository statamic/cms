<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class OpinionScale extends Fieldtype
{
    protected $selectable = false;

    public function preload()
    {
        return [
            'scaleValues' => $this->scaleValues(),
        ];
    }

    public function extraRenderableFieldData(): array
    {
        return [
            'scale_values' => $this->scaleValues(),
        ];
    }

    private function scaleValues(): array
    {
        return range($this->config('min'), $this->config('max'));
    }
}
