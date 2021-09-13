<?php

namespace Tests\Antlers\Runtime;

use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\ParserTestCase;

class OnceTest extends ParserTestCase
{
    public function test_once_block_evaluates_once_inside_a_loop_and_tag_contexts()
    {
        $template = <<<'EOT'
{{ loop from="1" to="10" }}
{{ once }}
<p>Once Block Before</p>
{{ title }} -- {{ value }}
<p>Once Block After</p>

{{ /once }}
{{ /loop }}
EOT;

        $expected = <<<'EOT'
<p>Once Block Before</p>
Test Title -- 1
<p>Once Block After</p>
EOT;

        $results = trim($this->renderString($template, ['title' => 'Test Title'], true));
        $this->assertSame(StringUtilities::normalizeLineEndings($expected), $results);
    }
}
