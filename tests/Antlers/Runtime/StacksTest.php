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

    public function test_stacks_and_sections_work_from_partials()
    {
        $template = <<<'EOT'
{{ partial:stacksections }}
{{ stack:the_stack }}
{{ yield:the_section }}

{{ push:the_stack }}<More stack content>{{ /push:the_stack }}

EOT;

        $expected = <<<'EOT'
<The stack content><More stack content>
<Section Content>
EOT;

        $this->assertSame($expected, trim($this->renderString($template, [], true)));

        // The section content should be replaced with "Different Section Content" since it is processed _after_ the partial.
        $template = <<<'EOT'
{{ partial:stacksections }}
{{ stack:the_stack }}
{{ yield:the_section }}

{{ push:the_stack }}<More stack content>{{ /push:the_stack }}
{{ section:the_section }}<Different Section Content>{{ /section:the_section }}
EOT;

        $expected = <<<'EOT'
<The stack content><More stack content>
<Different Section Content>
EOT;

        $this->assertSame($expected, trim($this->renderString($template, [], true)));
    }
}
