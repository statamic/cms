<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\Icon;
use Statamic\Fields\Fieldtype;

class FormBanner extends Fieldtype
{
    protected $selectable = false;

    public function extraRenderableFieldData(): array
    {
        $icon = $this->config('icon');

        return [
            'heading' => $this->config('display'),
            'text' => $this->config('text'),
            'icon' => $icon ? Icon::default()->get($icon) : null,
        ];
    }
}
