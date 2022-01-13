<?php

namespace Tests\Antlers\Runtime;

use Tests\Antlers\ParserTestCase;

class MethodStyleModifiersTest extends  ParserTestCase
{

    public function test_using_method_syntax_works_for_modifiers_with_empty_arg_group()
    {
        $template = <<<'EOT'
{{ title | upper() }}
EOT;

        $this->assertSame('HELLO, WILDERNESS', $this->renderString($template, [
            'title' => 'Hello, Wilderness'
        ], true));
    }

    public function test_ensure_arg_group_is_still_optional()
    {
        $template = <<<'EOT'
{{ title | upper }}
EOT;

        $this->assertSame('HELLO, WILDERNESS', $this->renderString($template, [
            'title' => 'Hello, Wilderness'
        ], true));
    }

    public function test_method_syntax_allows_for_chained_modifiers()
    {
        $template = <<<'EOT'
{{ title | upper() | lower() }}
EOT;

        $this->assertSame('hello, wilderness', $this->renderString($template, [
            'title' => 'Hello, Wilderness'
        ], true));
    }

    public function test_method_syntax_captures_context_variables()
    {
        $template = <<<'EOT'
{{ title | upper() | ensure_right('To the right!') | ensure_left(toTheLeft) }}
EOT;

        $result = $this->renderString($template, [
            'title' => 'The Title.',
            'toTheLeft' => 'The left value.'
        ], true);

        $this->assertSame('The left value.THE TITLE.To the right!', $result);
    }
}