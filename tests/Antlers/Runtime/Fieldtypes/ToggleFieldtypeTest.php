<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Tests\Antlers\ParserTestCase;

class ToggleFieldtypeTest extends ParserTestCase
{
    public function test_render_toggle_fieldtype()
    {
        $this->runFieldTypeTest('toggle');
    }
}
