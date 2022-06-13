<?php

namespace Tests\Antlers\Runtime;

use Tests\Antlers\ParserTestCase;

class UnlessTest extends ParserTestCase
{
    public function test_elseunless_conditions_does_not_cause_error()
    {
        $template = <<<'EOT'
{{ unless first }}First Branch{{ elseunless last }}Second Branch{{ /unless }}
EOT;

        $this->assertSame('First Branch', $this->renderString($template, [
            'first' => false,
            'last' => false,
        ]));
    }
}
