<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Tests\Antlers\ParserTestCase;

class BardFieldtypeTest extends ParserTestCase
{
    public function test_render_bard_field()
    {
        $this->runFieldTypeTest('bard');
    }
}
