<?php

namespace Tests\Antlers\Parser;

use Statamic\View\Antlers\Language\Errors\TypeLabeler;
use Statamic\View\Antlers\Language\Exceptions\SyntaxErrorException;
use Tests\Antlers\ParserTestCase;

class ParserErrorsTest extends ParserTestCase
{
    public function test_weird_operator_order_throws_exception()
    {
        $this->assertThrowsParserError('{{ test %% 3 }}');
    }

    public function test_incomplete_parameter_throws_exception()
    {
        $this->assertThrowsParserError('{{ nav parm="asdf }}');
    }

    public function test_incomplete_single_quote_string_throws_exception()
    {
        $this->assertThrowsParserError('{{ nav parm=\'"asdf" }}');
    }

    public function test_dangling_else_throws_exception()
    {
        $this->assertThrowsParserError('{{ if }} {{ else }}');
    }

    public function test_incorrect_condition_order_throws_exception()
    {
        $this->assertThrowsParserError('{{ if }} {{ else }} {{ elseif }} {{ /if }}');
    }

    public function test_dangling_else_unless_throws_exception()
    {
        $this->assertThrowsParserError('{{ unless true }} {{ elseunless }}');
    }

    public function test_empty_if_throws_exception()
    {
        $this->assertThrowsParserError('{{ if }} {{ /if }}');
    }

    public function test_empty_else_if_throws_exception()
    {
        $this->assertThrowsParserError('{{ if true == true }} {{ elseif }} {{ /if }}');
    }

    public function test_empty_unless_throws_exception()
    {
        $this->assertThrowsParserError('{{ unless }} {{ /unless }}');
    }

    public function test_empty_else_unless_throws_exception()
    {
        $this->assertThrowsParserError('{{ unless true }} {{ elseunless }} {{ /unless }}');
    }

    public function test_illegal_language_operator_throws_exception()
    {
        $this->assertThrowsParserError('{{ notalangoperator "hello" }}');
    }

    public function test_unenclosed_group_by_logic_group_throws_exception()
    {
        $this->assertThrowsParserError('{{ articles groupby (x => x.name) }}');
    }

    public function test_unenclosed_group_by_aliased_logic_group_throws_exception()
    {
        $this->assertThrowsParserError('{{ articles groupby (x => x.name) as "group_name" }}');
    }

    public function test_assignment_to_scalar_throws_exception()
    {
        $this->assertThrowsParserError('{{ 25 %= 5 }}');
    }

    public function test_missing_logic_group_close_throws_error_parsing_array()
    {
        $this->assertThrowsParserError('{{ arr(1,2,3 }}');
    }

    public function test_array_as_key_throws_error()
    {
        $this->assertThrowsParserError('{{ arr(arr(1) => 1) }}');
    }

    public function test_consecutive_dangling_array_elements_throws_error()
    {
        $this->assertThrowsParserError('{{ arr(1,2,,) }}');
    }

    public function test_missing_key_value_value_throws_error()
    {
        $this->assertThrowsParserError('{{ arr(1,2 =>) }}');
    }

    public function test_missing_key_value_name_throws_error()
    {
        $this->assertThrowsParserError('{{ arr( => 3) }}');
    }

    public function test_missing_key_value_name_inside_array_throws_error()
    {
        $this->assertThrowsParserError('{{ arr("one" => 3, => 5) }}');
    }

    public function test_incorrectly_chained_methods_throws_error_colon_syntax()
    {
        $this->assertThrowsParserError(<<<'EOT'
{{
    datetime:parse("October 12, 2001")::
            addDays(10):
            toAtomString()
}}
EOT
        );
    }

    public function test_incorrectly_chained_methods_throws_error_dot_syntax()
    {
        $this->assertThrowsParserError(<<<'EOT'
{{
    datetime:parse("October 12, 2001").
            addDays(10).
            toAtomString()
}}
EOT
        );
    }

    public function test_runtime_type_labeler()
    {
        $this->assertSame('string', TypeLabeler::getPrettyRuntimeTypeName('hello'));
        $this->assertSame('string', TypeLabeler::getPrettyRuntimeTypeName('1234'));
        $this->assertSame('numeric', TypeLabeler::getPrettyRuntimeTypeName(1234));
        $this->assertSame('numeric', TypeLabeler::getPrettyRuntimeTypeName(1234.0));
        $this->assertSame('null', TypeLabeler::getPrettyRuntimeTypeName(null));
        $this->assertSame('bool', TypeLabeler::getPrettyRuntimeTypeName(true));
        $this->assertSame('bool', TypeLabeler::getPrettyRuntimeTypeName(false));
    }

    public function test_neighboring_strings_throws_parser_error_in_modifiers()
    {
        $this->assertThrowsParserError(<<<'EOT'
{{ some_value | modifier: "string" "string" }}
EOT
        );
    }

    public function test_neighboring_numeric_throws_parser_error_in_modifiers()
    {
        $this->assertThrowsParserError(<<<'EOT'
{{ some_value | modifier: "string" 123 }}
EOT
        );
    }

    public function test_line_offsets_are_respected()
    {
        $template = <<<'EOT'
---
test: 'hello'
hello: 'world'
hello1: 'world'
hello2: 'world'
hello3: 'world'
---
line
line
line

{{ partial:withfrontmatter }}
EOT;

        try {
            $this->renderString($template, [], true);
            $this->fail('No exception thrown.');
        } catch (SyntaxErrorException $exception) {
            if ($exception->node == null) {
                $this->fail('Node is null');
            }

            // The error should be produced on line 20 of __fixtures__/views/partials/_anotherpartial.antlers.html
            // This partial is parsed after it is included from __fixtures__/views/partials/_withfrontmatter.antlers.html
            $this->assertSame(20, $exception->node->startPosition->line);
        }
    }

    public function test_modifier_method_syntax_with_extra_tokens_throws_error()
    {
        $this->assertThrowsParserError('{{ title | modifier_name(param1, param2) : something_else }}');
    }

    public function test_shorthand_parameters_cannot_have_special_characters()
    {
        $this->assertThrowsParserError('{{ tag_name :$thing="that" }}');
    }

    public function test_shorthand_parameters_cannot_start_with_numbers()
    {
        $this->assertThrowsParserError('{{ tag_name :$1thing }}');
    }

    public function test_incomplete_shorthand_parameters_throws_error()
    {
        $this->assertThrowsParserError('{{ tag_name :$ }}');
    }

    public function test_incomplete_shorthand_parameters_throws_error_two()
    {
        $this->assertThrowsParserError('{{ tag_name :$}}');
    }
}
