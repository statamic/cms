<?php

namespace Statamic\Fields\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Markdown extends Fieldtype
{
    protected $configFields = [
        'container' => ['type' => 'asset_container'],
        'folder' => ['type' => 'asset_folder'],
        'restrict' => ['type' => 'toggle']
    ];

    public function augment($value)
    {
        return markdown($value);
    }
}
