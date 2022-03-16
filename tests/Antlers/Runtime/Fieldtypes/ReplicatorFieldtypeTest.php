<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Tests\Antlers\ParserTestCase;

class ReplicatorFieldtypeTest extends ParserTestCase
{
    public function test_render_replicator_field()
    {
        $this->runFieldTypeTest('replicator');
    }
}
