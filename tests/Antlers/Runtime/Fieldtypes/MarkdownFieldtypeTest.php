<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Tests\Antlers\ParserTestCase;

class MarkdownFieldtypeTest extends ParserTestCase
{
    public function test_render_markdown_fieldtype()
    {
        $this->runFieldTypeTest('markdown');
    }
}
