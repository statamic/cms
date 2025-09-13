<?php

namespace Tests\Antlers\Parser;

use Tests\Antlers\ParserTestCase;

class DirectivesTest extends ParserTestCase
{
    public function test_directives_can_be_escaped()
    {
        $template = <<<'EOT'
@@props
@@aware
s
{{ title }}
EOT;

        $expected = <<<'EXECTED'
@props
@aware
s
The Title
EXECTED;

        $this->assertSame(
            $expected,
            $this->renderString($template, ['title' => 'The Title'])
        );
    }

    public function test_directives_must_contain_args()
    {
        $this->expectExceptionMessage('Missing arguments for @props directive');

        $this->renderString('@props');
    }

    public function test_directives_args_must_be_finished()
    {
        $this->expectExceptionMessage('Incomplete arguments for @props directive');

        $this->renderString('@props ("this isnt()", "done!"');
    }
}
