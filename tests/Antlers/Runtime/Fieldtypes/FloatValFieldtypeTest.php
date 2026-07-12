<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Tests\Antlers\ParserTestCase;

class FloatValFieldtypeTest extends ParserTestCase
{
    public function test_render_float_fieldtype()
    {
        $this->runFieldTypeTest('float');
    }
}
