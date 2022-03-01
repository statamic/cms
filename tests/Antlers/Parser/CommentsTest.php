<?php

namespace Tests\Antlers\Parser;

use Tests\Antlers\ParserTestCase;

class CommentsTest extends ParserTestCase
{
    public function test_antlers_in_comments_does_not_get_parsed_or_trigger_errors()
    {
        $template = <<<'EOT'
{{# {{ validate | contains:required ?= "required" }} #}}
EOT;

        $this->assertSame('', $this->renderString($template));
    }
}
