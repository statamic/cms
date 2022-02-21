<?php

namespace Tests\Antlers\Runtime;

use Tests\Antlers\ParserTestCase;

class StacksTest extends ParserTestCase
{
    public function test_basic_stacks_work()
    {
        $template = <<<'EOT'
BEFORE{{ stack:scripts }}AFTER

{{ push:scripts }}
Push 1
{{ /push:scripts }}

{{ prepend:scripts }}
Prepend 1
{{ /prepend:scripts }}

{{ push:scripts }}
Push 2
{{ /push:scripts }}

{{ prepend:scripts }}
Prepend 2
{{ /prepend:scripts }}
EOT;

        $this->assertSame('BEFOREPrepend 2Prepend 1Push 1Push 2AFTER', trim($this->renderString($template, [])));
    }

    public function test_stacks_from_partials()
    {
        $template = <<<'EOT'
BEFORE{{ stack:scripts }}AFTER

{{ partial:stacks }}
EOT;

        $this->assertSame('BEFOREPrepend 2Prepend 1Push 1Push 2AFTER', trim($this->renderString($template, [], true)));
    }
}
