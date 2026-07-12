<?php

namespace Tests\Antlers\Runtime;

use Tests\Antlers\ParserTestCase;

class StructuresTest extends ParserTestCase
{
    public function test_dangling_expressions_get_grouped_into_semantic_groups_and_evaluated()
    {
        $this->assertSame(10, (int) $this->renderString('{{
            test = 5;
            test += 5;
            test
        }}'));
    }
}
