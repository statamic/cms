<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Tests\Antlers\ParserTestCase;

class CodeFieldtypeTest extends ParserTestCase
{
    public function test_render_code_fieldtype()
    {
        $this->runFieldTypeTest('code');
    }
}
