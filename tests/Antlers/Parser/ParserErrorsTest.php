<?php

namespace Tests\Antlers\Parser;

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

    public function test_missing_pluck_into_target_throws_exception()
    {
        $this->assertThrowsParserError('{{ test = topics pluck_into  }}{{ /test }}');
    }

    public function test_invalid_pluck_into_variable_reference_throws_exception()
    {
        $this->assertThrowsParserError('{{ test = topics pluck_into "string" }}{{ /test }}');
    }

    public function test_invalid_eoi_pluck_into_throw_exception()
    {
        $this->assertThrowsParserError('{{ test = topics pluck_into articles }}{{ /test }}');
    }

    public function test_pluck_into_empty_logic_group_throws_exception()
    {
        $this->assertThrowsParserError('{{ test = topics pluck_into articles () }}{{ /test }}');
    }

    public function test_too_many_temp_vars_in_pluck_into_throws_exception()
    {
        $this->assertThrowsParserError('{{ test = topics pluck_into articles (x, y, z => x.id) }}{{ /test }}');
    }

    public function test_invalid_as_keyword_throws_exception_for_pluck_into()
    {
        $this->assertThrowsParserError('{{ test = topics pluck_into articles (x, y => x.id) asdf "new_name" }}{{ /test }}');
    }

    public function test_pluck_into_ambiguous_variable_name_throws_exception_one()
    {
        $this->assertThrowsParserError('{{ test = topics pluck_into articles (x, x => x.id) as "new_name" }}{{ /test }}');
    }

    public function test_pluck_into_ambiguous_variable_name_throws_exception_two()
    {
        $this->assertThrowsParserError('{{ test = articles pluck_into articles (articles.id arr_contains articles.id) as "new_name" }}{{ /test }}');
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
    datetime:parse("October 12, 2001"):
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
}
