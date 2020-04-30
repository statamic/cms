<?php

namespace Tests\Fakes\Fieldtypes;

use Facades\Tests\Factories\FieldsetFactory;
use Statamic\Extend\Fieldtype;

class FieldtypeWithPreprocessableConfigField extends Fieldtype
{
    public function getConfigFieldset()
    {
        return FieldsetFactory::withFields([
            ['handle' => 'test', 'field' => ['type' => 'baz']],
        ])->create();
    }
}
