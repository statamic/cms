<?php

namespace Tests\Antlers\Runtime;

use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\ParserTestCase;

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
