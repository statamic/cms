<?php

namespace Tests\Antlers\Parser;

use PHPUnit\Framework\Attributes\Test;
use Tests\Antlers\ParserTestCase;

class StringsTest extends ParserTestCase
{
    public function test_strings_with_html_are_not_parsed_as_antlers_parameters()
    {
        $input = <<<'EOT'
{{ test = test + '<span style="' + var + '">Test!</span>' }}{{ test }}
EOT;
        $this->assertSame('Prefix<span style="hello">Test!</span>', $this->renderString($input, ['var' => 'hello', 'test' => 'Prefix'], true));
    }

    public function test_strings_can_be_combined_using_addition_assignment()
    {
        // The chained variable name without anything else is an implicit return
        // and is equivalent to adding {{ var }} to the end of the template.
        //
        // This implicit return:
        //     {{ var += ' test!'; var }}
        // Is equivalent to:
        //     {{ var += ' test!'; }}{{ var }}
        $input = <<<'EOT'
{{ var += ' test!'; var }}
EOT;

        $this->assertSame('String Value test!', $this->renderString($input, ['var' => 'String Value']));
    }

    public function test_empty_strings_can_be_combined_using_addition_assignment()
    {
        $input = <<<'EOT'
{{ test = ''; test += 'hello, world'; test }}
EOT;

        $this->assertSame('hello, world', $this->renderString($input));
    }

    public function test_braces_can_be_escaped_inside_string_literals()
    {
        $input = <<<'EOT'
{{ var = '@{@{@}@}'; }}{{ var }}
EOT;

        $this->assertSame('{{}}', $this->renderString($input));
    }

    public function test_escape_sequences_are_replaced_inside_the_environment()
    {
        $input = <<<'EOT'
{{ if title | starts_with('@{') }}Yes{{ else }}No{{ /if }}
EOT;

        $this->assertSame('Yes', $this->renderString($input, ['title' => '{The Title}']));

        $input = <<<'EOT'
{{ if title | starts_with:'@{' }}Yes{{ else }}No{{ /if }}
EOT;

        $this->assertSame('Yes', $this->renderString($input, ['title' => '{The Title}']));
    }
}
