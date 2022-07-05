<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Tests\Antlers\ParserTestCase;

class ButtonGroupFieldtypeTest extends ParserTestCase
{
    public function test_render_button_group()
    {
        $this->runFieldTypeTest('button_group');
    }
}
