<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Tests\Antlers\ParserTestCase;

class TableFieldtypeTest extends ParserTestCase
{
    public function test_render_table_fieldtype()
    {
        $this->runFieldTypeTest('table');
    }
}
