<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Tests\Antlers\ParserTestCase;

class YamlFieldtypeTest extends ParserTestCase
{
    public function test_render_yaml_fieldtype()
    {
        $this->runFieldTypeTest('yaml');
    }
}
