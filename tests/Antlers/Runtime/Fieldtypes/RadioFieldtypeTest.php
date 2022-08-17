<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Tests\Antlers\ParserTestCase;

class RadioFieldtypeTest extends ParserTestCase
{
    public function test_render_radio_fieldtype()
    {
        $this->runFieldTypeTest('radio');
    }
}
