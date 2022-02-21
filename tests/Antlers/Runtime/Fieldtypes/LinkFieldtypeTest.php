<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Tests\Antlers\ParserTestCase;

class LinkFieldtypeTest extends ParserTestCase
{
    public function test_render_link_fieldtype()
    {
        $this->runFieldTypeTest('link_field');
    }
}
