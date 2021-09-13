<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Tests\Antlers\ParserTestCase;

class ColorFieldtypeTest extends ParserTestCase
{
    public function test_render_color_fieldtype()
    {
        $this->runFieldTypeTest('color');
    }
}
