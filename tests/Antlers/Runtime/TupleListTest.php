<?php

namespace Tests\Antlers\Runtime;

use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\ParserTestCase;

class TupleListTest extends ParserTestCase
{
    public function test_tuple_lists_keyword_syntax()
    {
        $template = <<<'EOT'
{{

items = list(
    name,    color, type;
    'Apple', 'red', 'fruit';
    'Hammer', 'brown', 'tool';
    'Orange', 'orange', 'fruit';
    'Lettuce', 'green', 'vegetable'
)
}}
{{ name }} -- {{ color }} -- {{ type }}
{{ /items }}
EOT;

        $expected = <<<'EOT'

Apple -- red -- fruit

Hammer -- brown -- tool

Orange -- orange -- fruit

Lettuce -- green -- vegetable

EOT;

        $results = $this->renderString($template);
        $this->assertSame(StringUtilities::normalizeLineEndings($expected), StringUtilities::normalizeLineEndings($results));
    }

    public function test_tuple_lists_are_parsed_and_can_be_iterated()
    {
        $template = <<<'EOT'
{{

items = list(
    name,    color, type;
    'Apple', 'red', 'fruit';
    'Hammer', 'brown', 'tool';
    'Orange', 'orange', 'fruit';
    'Lettuce', 'green', 'vegetable'
)
}}
{{ name }} -- {{ color }} -- {{ type }}
{{ /items }}
EOT;

        $expected = <<<'EOT'

Apple -- red -- fruit

Hammer -- brown -- tool

Orange -- orange -- fruit

Lettuce -- green -- vegetable

EOT;

        $results = $this->renderString($template);
        $this->assertSame(StringUtilities::normalizeLineEndings($expected), StringUtilities::normalizeLineEndings($results));
    }
}
