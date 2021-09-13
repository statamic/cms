<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Tests\Antlers\ParserTestCase;

class SelectFieldtypeTest extends ParserTestCase
{
    public function test_render_select_fieldtype()
    {
        $this->runFieldTypeTest('select_field');
    }
}
