<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Tests\Antlers\ParserTestCase;

class IntegerFieldtypeTest extends ParserTestCase
{
    public function test_render_integer_fieldtype()
    {
        $this->runFieldTypeTest('integer_field');
    }
}
