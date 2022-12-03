<?php

namespace Tests\Antlers\Runtime;

use Statamic\Tags\Tags;
use Tests\Antlers\ParserTestCase;

class DynamicAntlersTest extends ParserTestCase
{
    public function test_incorrectly_wrapped_dynamic_names_does_not_result_in_stack_error()
    {
        $data = [
            'prefix' => 'the_',
            'variable_name' => 'value',
            'the_value' => 'Hello, wilderness',
        ];

        // The "better" way to write this (when it's the tag/variable name) is just {{ {prefix}{variable_name} }}

        $this->assertSame('Hello, wilderness', $this->renderString('{{ {{prefix}{variable_name}} }}', $data));
        $this->assertSame('Hello, wilderness', $this->renderString('{{ {{{prefix}{variable_name}}} }}', $data));
        $this->assertSame('Hello, wilderness', $this->renderString('{{ {{{{{{prefix}{variable_name}}}}}} }}', $data));
    }

    public function test_simple_dynamic_variables()
    {
        $data = [
            'variable_name' => 'array_value',
            'array_value' => [
                ['title' => 'First'],
                ['title' => 'Second'],
            ],
        ];

        $template = <<<'EOT'
{{ {variable_name} }}<{{ title }}>{{ /{variable_name} }}
EOT;

        $this->assertSame('<First><Second>', $this->renderString($template, $data));
    }

    public function test_prefixed_variables()
    {
        $template = <<<'EOT'
{{
    value = 'just a value';
    the_value = 'this is THE value';
}}

<1:{{ {prefix}value }}>

{{ prefix = 'the_'; }}

<2:{{ {prefix}value }}>

{{ prefix = 'the'; }}

<3:{{ {prefix}value }}>
<4:{{ {prefix ?= '{prefix}_'}value }}
EOT;

        // 1: The dynamic variable results in "{{ value }}"
        // 2: The dynamic variable results in "{{ the_value }}"
        // 3: The dynamic variable results in "{{ thevalue }}", which should return an empty string/null
        // 4: The dynamic variable results in "{{ the_value }}", which should return "this is THE value"

        $expected = <<<'EXPECTED'
<1:just a value>



<2:this is THE value>



<3:>
<4:this is THE value
EXPECTED;

        $this->assertSame($expected, trim($this->renderString($template)));

        // Dynamic expressions wrapped in single or double quotes
        // is one of the ways to trigger string concatenation
        // mode. This test ensures that dynamic variables
        // work without breaking concatenation mode.

        // Note to source divers:
        //   Please do not do things like {{{{{{six}}}}}}
        //   This is just to test the Runtime's ability
        //   to bubble values up from deeply nested
        //   interpolation regions. - JK

        $template = <<<'EOT'
{{
    one = 'one';
    two = 'two';
    three = 'three';
    four = 'four';
    five = 'five';
    six = 'six';
    one_two_three_four_five_six = 'Oh hai, Mark!';
}}
{{ {one}_{two}_{three}_{four}_{five}_{six} }}
{{ {one}_{{two}}_{{{three}}}_{{{{four}}}}_{{{{{five}}}}}_{{{{{{six}}}}}} }}
{{ '{one}_{two}_{three}_{four}_{five}_{six}' }}
{{ "{one}_{two}_{three}_{four}_{five}_{six}" }}
EOT;

        $expected = <<<'EXPECTED'
Oh hai, Mark!
Oh hai, Mark!
one_two_three_four_five_six
one_two_three_four_five_six
EXPECTED;

        $this->assertSame($expected, trim($this->renderString($template)));
    }

    public function test_dynamic_variable_names_within_nested_variable_interpolation()
    {
        // To use dynamic variable names within another expression,
        // we need to wrap the entire dynamic region in curly braces.
        // In the following example, the dynamic part is
        // the {{prefix}value} part. The {prefix} will be evaluated
        // first, and then the entire value will be used as the
        // variable name. In this scenario, the "value" will
        // be treated as a literal within the nested value.

        $template = <<<'EOT'
{{
    one = 'one';
    prefix = 'the_';
    the_value = 'THE value';
    value = 'just a value';
}}

{{ one + '-' + '{{prefix}value}' + '-' + value + '-{value}' }}
EOT;

        $this->assertSame('one-THE value-just a value-just a value', trim($this->renderString($template)));

        // In this test, the nested interpolation looks like this:
        // {{prefix}{value}}
        // Antlers will evaluate both the {prefix} and {value}
        // regions independently, and then concatenate the results.
        // The concatenated result will be used as the variable name.

        $template = <<<'EOT'
{{
    one = 'one';
    prefix = 'the_';
    the_value = 'THE value';
    value = 'value';
}}

{{ one + '-' + {{prefix}{value}} + '-' + value + '-{value}' }}
EOT;

        $this->assertSame('one-THE value-value-value', trim($this->renderString($template)));

        // Ensure that dynamic variable regions inside strings are parsed.

        $template = <<<'EOT'
{{
    one = 'one';
    prefix = 'the_';
    the_value = 'THE value';
    value = 'value';
}}

{{ one + '-' + '{{prefix}{value}}' + '-' + value + '-{value}' }}
EOT;

        $this->assertSame('one-THE value-value-value', trim($this->renderString($template)));
    }

    public function test_dynamic_variables_with_modifiers()
    {
        $this->assertSame('LOWER', $this->renderString('{{ {prefix}value | upper }}', [
            'value' => 'wrong one',
            'prefix' => 'the_',
            'the_value' => 'lower',
        ], true));

        $this->assertSame('LOWER', $this->renderString('{{ {prefix}value upper="true" }}', [
            'value' => 'wrong one',
            'prefix' => 'the_',
            'the_value' => 'lower',
        ], true));
    }

    public function test_dynamic_tag_names()
    {
        (new class extends Tags
        {
            public static $handle = 'test_tag';

            public function wildcard($method)
            {
                $data = [];

                for ($i = 0; $i < 3; $i++) {
                    $data[] = ['title' => $method.'-'.$i];
                }

                return $data;
            }
        })::register();

        $expected = '<1-0><1-1><1-2><2-0><2-1><2-2><3-0><3-1><3-2>';

        $template = <<<'EOT'
{{ range from="1" to="3" }}{{ test_tag:{{ value }} }}<{{ title }}>{{ /test_tag:{{ value }} }}{{ /range }}
EOT;
        $this->assertSame($expected, $this->renderString($template, [], true));

        $template = <<<'EOT'
{{ range from="1" to="3" }}{{ _my_variable = 'test_tag:{value}'; }}{{ {_my_variable} }}<{{ title }}>{{ /{_my_variable} }}{{ /range }}
EOT;
        $this->assertSame($expected, $this->renderString($template, [], true));

        $template = <<<'EOT'
{{ range from="1" to="3" }}{{ _first_var = 'test_tag'; }}{{ _second_var = '{_first_var}:{value}'; }}{{ {_second_var} }}<{{ title }}>{{ /{_second_var} }}{{ /range }}
EOT;
        $this->assertSame($expected, $this->renderString($template, [], true));
    }
}
