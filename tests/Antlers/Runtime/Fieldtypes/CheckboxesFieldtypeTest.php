<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Tests\Antlers\ParserTestCase;

class CheckboxesFieldtypeTest extends ParserTestCase
{
    public function test_render_checkboxes()
    {
        $this->runFieldTypeTest('checkboxes');
    }
}
