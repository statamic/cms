<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Tests\Antlers\ParserTestCase;

class TextareaFieldtypeTest extends ParserTestCase
{
    public function test_render_textarea_fieldtype()
    {
        $this->runFieldTypeTest('textarea');
    }
}
