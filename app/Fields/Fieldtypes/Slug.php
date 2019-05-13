<?php

namespace Statamic\Fields\Fieldtypes;

use Statamic\Fields\Fieldtypes\Text;

class Slug extends Text
{
    protected $configFields = [
        'generate' => ['type' => 'toggle', 'default' => true],
    ];
}
