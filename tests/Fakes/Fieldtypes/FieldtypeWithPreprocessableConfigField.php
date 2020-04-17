<?php

namespace Tests\Fakes\Fieldtypes;

use Statamic\Extend\Fieldtype;
use Facades\Tests\Factories\FieldsetFactory;

class FieldtypeWithPreprocessableConfigField extends Fieldtype
{
    public function getConfigFieldset()
    {
        return FieldsetFactory::withFields([
            ['handle' => 'test', 'field' => ['type' => 'baz']],
        ])->create();
    }
}
