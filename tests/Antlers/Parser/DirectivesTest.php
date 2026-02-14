<?php

namespace Tests\Antlers\Parser;

use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Text;
use Tests\Antlers\ParserTestCase;

class DirectivesTest extends ParserTestCase
{
    public function test_directives_can_be_escaped()
    {
        $template = <<<'EOT'
@@props
@@aware
s
{{ title }}
EOT;

        $expected = <<<'EXECTED'
@props
@aware
s
The Title
EXECTED;

        $this->assertSame(
            $expected,
            $this->renderString($template, ['title' => 'The Title'])
        );
    }

    public function test_directives_args_must_be_finished()
    {
        $this->expectExceptionMessage('Incomplete arguments for @props directive');

        $this->renderString('@props ("this isnt()", "done!"');
    }

    public function test_directives_dont_leave_extra_parenthesis()
    {
        $template = <<<'EOT'
@props([

])a

Hellow
EOT;

        $expected = <<<'EOT'
a

Hellow
EOT;

        $this->assertSame($expected, $this->renderString($template));
    }

    public function test_directive_word_collisions_dont_cause_errors()
    {
        $template = <<<'EOT'
1aware2 @aware {{ title }}
EOT;

        $expected = <<<'EOT'
1aware2 @aware The Title
EOT;

        $this->assertSame(
            $expected,
            $this->renderString($template, ['title' => 'The Title'])
        );
    }

    public function test_directive_keywords_without_args_are_literal()
    {
        $this->assertSame('@aware', $this->renderString('@aware'));
        $this->assertSame('@props', $this->renderString('@props'));
        $this->assertSame('@aware trailing', $this->renderString('@aware trailing'));
        $this->assertSame('leading @props', $this->renderString('leading @props'));
        $this->assertSame('leading @aware trailing', $this->renderString('leading @aware trailing'));
        $this->assertSame('@aware     ', $this->renderString('@aware     '));
    }

    public function test_cascade_directive_can_be_escaped()
    {
        $template = <<<'EOT'
@@cascade
{{ title }}
EOT;

        $expected = <<<'EOT'
@cascade
The Title
EOT;

        $this->assertSame(
            $expected,
            $this->renderString($template, ['title' => 'The Title'])
        );
    }

    public function test_directive_substrings_in_words_dont_cause_errors()
    {
        $template = <<<'EOT'
appropriate unaware cascading property {{ title }}
EOT;

        $expected = <<<'EOT'
appropriate unaware cascading property The Title
EOT;

        $this->assertSame(
            $expected,
            $this->renderString($template, ['title' => 'The Title'])
        );
    }

    public function test_multiple_argless_directives()
    {
        $template = <<<'EOT'
@aware @props {{ title }}
EOT;

        $expected = <<<'EOT'
@aware @props The Title
EOT;

        $this->assertSame(
            $expected,
            $this->renderString($template, ['title' => 'The Title'])
        );
    }

    public function test_directive_adjacent_to_antlers()
    {
        $template = <<<'EOT'
@aware{{ title }}
EOT;

        $expected = <<<'EOT'
@awareThe Title
EOT;

        $this->assertSame(
            $expected,
            $this->renderString($template, ['title' => 'The Title'])
        );
    }

    public function test_whitespace_variations_before_args()
    {
        $template = "@props\t(['key' => 'value']){{ title }}";

        $this->assertSame(
            'The Title',
            $this->renderString($template, ['title' => 'The Title'])
        );

        $template = "@props\n(['key' => 'value']){{ title }}";

        $this->assertSame(
            'The Title',
            $this->renderString($template, ['title' => 'The Title'])
        );
    }

    public function test_mixed_valid_and_argless_directives()
    {
        $template = <<<'EOT'
@props(['key' => 'value']) @aware {{ title }}
EOT;

        $expected = <<<'EOT'
 @aware The Title
EOT;

        $this->assertSame(
            $expected,
            $this->renderString($template, ['title' => 'The Title'])
        );
    }

    public function test_directive_at_end_of_template()
    {
        $template = <<<'EOT'
{{ title }} @aware
EOT;

        $expected = <<<'EOT'
The Title @aware
EOT;

        $this->assertSame(
            $expected,
            $this->renderString($template, ['title' => 'The Title'])
        );
    }

    public function test_antlers_rendered_field_with_directive_keywords()
    {
        $textFieldtype = new Text();
        $field = new Field('text_field', [
            'type' => 'text',
            'antlers' => true,
        ]);

        $textFieldtype->setField($field);
        $value = new Value('@aware some content @props here', 'text_field', $textFieldtype);

        $template = <<<'EOT'
{{ text_field }}
EOT;

        $this->assertSame(
            '@aware some content @props here',
            $this->renderString($template, ['text_field' => $value])
        );
    }

    public function test_all_escaped_directives_together()
    {
        $template = <<<'EOT'
@@props @@aware @@cascade
{{ title }}
EOT;

        $expected = <<<'EOT'
@props @aware @cascade
The Title
EOT;

        $this->assertSame(
            $expected,
            $this->renderString($template, ['title' => 'The Title'])
        );
    }

    public function test_argless_directive_with_later_parens()
    {
        $template = <<<'EOT'
@aware some text (parenthetical) {{ title }}
EOT;

        $expected = <<<'EOT'
@aware some text (parenthetical) The Title
EOT;

        $this->assertSame(
            $expected,
            $this->renderString($template, ['title' => 'The Title'])
        );
    }

    public function test_directive_adjacent_no_space_before_antlers()
    {
        $template = <<<'EOT'
@props{{ title }}
EOT;

        $expected = <<<'EOT'
@propsThe Title
EOT;

        $this->assertSame(
            $expected,
            $this->renderString($template, ['title' => 'The Title'])
        );
    }

    public function test_directive_in_no_parse_is_correctly_escaped()
    {
        $template = <<<'EOT'
{{ noparse }}@cascade{{ /noparse }}
EOT;

        $this->assertSame('@cascade', $this->renderString($template));
    }
}
