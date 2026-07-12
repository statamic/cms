<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Tests\Antlers\ParserTestCase;

class ArrayFieldtypeTest extends ParserTestCase
{
    public function test_render_array_dynamic()
    {
        $this->runFieldTypeTest('array_dynamic');
    }

    public function test_render_array_keyed()
    {
        $this->runFieldTypeTest('array_keyed');
    }
}
