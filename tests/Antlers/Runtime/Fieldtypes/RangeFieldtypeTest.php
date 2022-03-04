<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Tests\Antlers\ParserTestCase;

class RangeFieldtypeTest extends ParserTestCase
{
    public function test_render_range_fieldtype()
    {
        $this->runFieldTypeTest('range_field');
    }
}
