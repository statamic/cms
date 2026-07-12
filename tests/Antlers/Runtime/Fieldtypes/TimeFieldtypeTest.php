<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Tests\Antlers\ParserTestCase;

class TimeFieldtypeTest extends ParserTestCase
{
    public function test_render_time_fieldtype()
    {
        $this->runFieldTypeTest('time');
    }
}
