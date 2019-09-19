<?php

namespace Statamic\Fieldtypes;

use Statamic\Fieldtypes\Text;

class Slug extends Text
{
    protected $configFields = [
        'generate' => ['type' => 'toggle', 'default' => true],
    ];
}
