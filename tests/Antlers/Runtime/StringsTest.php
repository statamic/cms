<?php

namespace Tests\Antlers\Runtime;

use Tests\Antlers\ParserTestCase;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;

class StringsTest extends ParserTestCase
{
    public function test_string_escape_sequences_are_parsed()
    {
        $template = <<<'EOT'
<h1>{{ "hello\"\n\t\'\\\\, world." }}</h1>
EOT;

        $expected = <<<'EOT'
<h1>hello"
	'\\, world.</h1>
EOT;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            StringUtilities::normalizeLineEndings($this->renderString($template)));
    }
}
