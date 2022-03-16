<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Tests\Antlers\ParserTestCase;

class BardFieldtypeTest extends ParserTestCase
{
    public function test_render_bard_field()
    {
        $this->runFieldTypeTest('bard');
    }

    public function test_raw_parameter_style_modifier_can_be_used_on_values()
    {
        $this->runFieldTypeTest('bard', 'bard_raw_parameter_modifier');
    }
}
