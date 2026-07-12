<?php

namespace Tests\Antlers\Runtime;

use Illuminate\Support\Str;
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

    public function test_unless_with_vars()
    {
        $template = <<<'EOT'
{{ the_var = 1 }}

{{ unless {the_var} }}true{{ else }}false{{ /unless }}
{{ if ! {the_var} }}true{{ else }}false{{ /if }}
EOT;

        $this->assertSame(
            'false false',
            Str::squish($this->renderString($template))
        );
    }
}
