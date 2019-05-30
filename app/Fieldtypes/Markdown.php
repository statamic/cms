<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Markdown extends Fieldtype
{
    protected $configFields = [
        'container' => ['type' => 'asset_container', 'max_items' => 1],
        'folder' => ['type' => 'asset_folder', 'max_items' => 1],
        'restrict' => ['type' => 'toggle']
    ];

    public function augment($value)
    {
        return markdown($value);
    }
}
