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

    public function test_strings_ending_with_literal_backslash_dont_incorrectly_attempt_to_escape_end_delimiter()
    {
        $template = <<<'EOT'
{{ "\\" }}
EOT;

        $this->assertSame('\\', $this->renderString($template));
    }

    public function test_string_concat_works_without_printing_string_to_output()
    {
        $template = <<<'EOT'
{{
    test = 'hello';
    test .= ', world';
}}
EOT;

        $this->assertSame('', $this->renderString($template));
    }

    public function test_string_concat_pushes_data_to_assignments()
    {
        $template = <<<'EOT'
{{
    test = 'hello';
    test .= ', world';
}}{{ test }}
EOT;

        $this->assertSame('hello, world', $this->renderString($template));
    }

    public function test_string_value_resolution_applies_interpolations()
    {
        $data = [
            'title' => 'The title',
            'subtitle' => 'The subtitle',
        ];

        $template = <<<'EOT'
{{ my_string = '<{title}><{subtitle}><@{title}><@{subtitle}><@@{title}><@@{subtitle}>'; }}{{ my_string }}
EOT;

        $this->assertSame('<The title><The subtitle><{title}><{subtitle}><@{title}><@{subtitle}>', $this->renderString($template, $data));
    }
}
