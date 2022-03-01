<?php

namespace Tests\Antlers\Runtime;

use Statamic\Fields\LabeledValue;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Select;
use Statamic\Tags\Tags;
use Statamic\Taxonomies\TermCollection;
use Statamic\View\Antlers\Language\Exceptions\AntlersException;
use Tests\Antlers\Fixtures\Addon\Tags\VarTest;
use Tests\Antlers\ParserTestCase;

class ConditionLogicTest extends ParserTestCase
{
    public function test_negation_following_or_is_evaluated()
    {
        $template = '{{ if !first && first_row_headers || !first_row_headers }}yes{{ else }}no{{ /if }}';

        $this->assertSame('yes', $this->renderString($template, [
            'first_row_headers' => false,
        ]));
        $this->assertSame('no', $this->renderString($template, [
            'first_row_headers' => true,
            'first' => true,
        ]));
        $this->assertSame('yes', $this->renderString($template, [
            'first_row_headers' => false,
            'first' => true,
        ]));
        $this->assertSame('yes', $this->renderString($template, [
            'first_row_headers' => false,
            'first' => false,
        ]));
    }

    public function test_negation_equivalency()
    {
        $data = ['variable' => false];

        $this->assertFalse($this->evaluateRaw('!!variable', $data));
        $this->assertFalse($this->evaluateRaw('!!(variable)', $data));
        $this->assertFalse($this->evaluateRaw('!(!variable)', $data));

        $this->assertTrue($this->evaluateRaw('!variable', $data));
        $this->assertTrue($this->evaluateRaw('(!variable)', $data));
        $this->assertTrue($this->evaluateRaw('!(variable)', $data));
        $this->assertTrue($this->evaluateRaw('!!!variable', $data));

        $this->assertTrue($this->evaluateRaw('!variable && !variable', $data));
        $this->assertTrue($this->evaluateRaw('!variable && !!(!variable)', $data));
    }

    public function test_comparison_operators()
    {
        $this->assertTrue($this->evaluateRaw('10 > 5'));
        $this->assertTrue($this->evaluateRaw('10 > 5 && 5 > 2'));
        $this->assertFalse($this->evaluateRaw('10 > 5 && 5 < 2'));

        $this->assertFalse($this->evaluateRaw('5 < 5'));
        $this->assertTrue($this->evaluateRaw('5 <= 5'));

        $this->assertFalse($this->evaluateRaw('5 > 5'));
        $this->assertTrue($this->evaluateRaw('5 >= 5'));

        $this->assertFalse($this->evaluateRaw('3 + 2 > 5'));
        $this->assertTrue($this->evaluateRaw('3 + 2 >= 5'));

        $this->assertTrue($this->evaluateRaw('5 == "5"'));
        $this->assertFalse($this->evaluateRaw('5 === "5"'));

        $this->assertTrue($this->evaluateRaw('5 != 4'));
        $this->assertFalse($this->evaluateRaw('5 !== 5'));
        $this->assertTrue($this->evaluateRaw('5 !== "5"'));

        $this->assertTrue($this->evaluateRaw('5 < 10'));
        $this->assertTrue($this->evaluateRaw('5 <= 10'));
        $this->assertFalse($this->evaluateRaw('5 < 3'));
        $this->assertFalse($this->evaluateRaw('5 <= 3'));
    }

    public function test_modifiers_in_multiple_conditions()
    {
        $data = ['var' => [1, 2, 3]];

        $template = <<<'EOT'
{{ if (var | count) > 1 && (var | count) < 4 }}yes{{ else }}no{{ /if }}
EOT;

        $this->assertSame('yes', $this->renderString($template, $data));
        $this->assertSame('no', $this->renderString($template, [
            'var' => [1, 2, 3, 4, 5, 6, 7],
        ]));
    }

    public function test_shorthand_logical_and_equivalency()
    {
        $this->assertSame('yes', $this->renderString('{{ if true & true }}yes{{ else }}no{{ /if }}'));
        $this->assertSame('yes', $this->renderString('{{ if true && true }}yes{{ else }}no{{ /if }}'));
    }

    public function test_switch_operator()
    {
        $template = <<<'EOT'
{{ switch (
    (value == 1) => {'First'},
    (value == 13) => {'Second'},
    () => {"Default"}
 ) }}
EOT;

        $this->assertSame('First', $this->renderString($template, ['value' => 1]));
        $this->assertSame('Second', $this->renderString($template, ['value' => 13]));
        $this->assertSame('Default', $this->renderString($template, ['value' => 2]));
        $this->assertSame('Default', $this->renderString($template, ['value' => 23]));
    }

    public function test_switch_invalid_default_position_throws_exception()
    {
        $this->expectException(AntlersException::class);
        $template = <<<'EOT'
{{ switch (
    () => {"Default"},
    (value == 1) => {'First'},
    (value == 13) => {'Second'},
 ) }}
EOT;

        $this->renderString($template, []);
    }

    public function test_missing_arg_separator_after_first_case_statement_throws_exception()
    {
        $this->expectException(AntlersException::class);
        $template = <<<'EOT'
{{ switch (
    () => {"Default"}
    (value == 1) => {'First'},
    (value == 13) => {'Second'},
 ) }}
EOT;

        $this->renderString($template, []);
    }

    public function test_missing_arg_separator_after_other_case_statement_throws_exception()
    {
        $this->expectException(AntlersException::class);
        $template = <<<'EOT'
{{ switch (
    () => {"Default"},
    (value == 1) => {'First'},
    (value == 31) => {'Another'}
    (value == 13) => {'Second'},
 ) }}
EOT;

        $this->renderString($template, []);
    }

    public function test_switch_operator_is_assignable()
    {
        $template = <<<'EOT'
{{ my_var = switch ((value == 1) => {'First'}, (value == 13) => {'Second'}, () => {"Default"} ) }}Result: {{ my_var }}
EOT;

        $this->assertSame('Result: First', $this->renderString($template, ['value' => 1]));
        $this->assertSame('Result: Second', $this->renderString($template, ['value' => 13]));
        $this->assertSame('Result: Default', $this->renderString($template, ['value' => 2]));
        $this->assertSame('Result: Default', $this->renderString($template, ['value' => 23]));
    }

    public function test_switch_operator_can_use_multiple_scope_variables()
    {
        $template = <<<'EOT'
{{ my_var = switch (
       (value == 1 && value_two == 2) => {'First'},
       (value == 13 && value_two == 1) => {'Second'},
       () => {"Default"}
  ) }}Result: {{ my_var }}
EOT;

        $this->assertSame('Result: Second', $this->renderString($template, [
            'value' => 13,
            'value_two' => 1,
        ]));
        $this->assertSame('Result: Default', $this->renderString($template, [
            'value' => 1,
            'value_two' => 1,
        ]));
        $this->assertSame('Result: First', $this->renderString($template, [
            'value' => 1,
            'value_two' => 2,
        ]));

        $template = <<<'EOT'
{{ my_var = switch (
       (value == 1 && value_two == 2) => 'First',
       (value == 13 && value_two == 1) => 'Second',
       () => "Default"
  )
}}Result: {{ my_var }}
EOT;

        $this->assertSame('Result: Second', $this->renderString($template, [
            'value' => 13,
            'value_two' => 1,
        ]));
        $this->assertSame('Result: Default', $this->renderString($template, [
            'value' => 1,
            'value_two' => 1,
        ]));
        $this->assertSame('Result: First', $this->renderString($template, [
            'value' => 1,
            'value_two' => 2,
        ]));
    }

    public function test_switch_operator_is_assignable_from_interpolated_expression()
    {
        $template = <<<'EOT'
{{ my_var = {switch ((value == 1) => {'First'}, (value == 13) => {'Second'}, () => {"Default"} )} }}Result: {{ my_var }}
EOT;

        $this->assertSame('Result: First', $this->renderString($template, ['value' => 1]));
        $this->assertSame('Result: Second', $this->renderString($template, ['value' => 13]));
        $this->assertSame('Result: Default', $this->renderString($template, ['value' => 2]));
        $this->assertSame('Result: Default', $this->renderString($template, ['value' => 23]));
    }

    public function test_deferred_expressions_are_evaluated_correctly_from_ternary_conditions_relaxed()
    {
        (new class extends Tags
        {
            public static $handle = 'tag-pages';

            public function index()
            {
                return [
                    ['title' => 'Page 1'],
                    ['title' => 'Page 2'],
                    ['title' => 'Page 3'],
                ];
            }
        })::register();

        (new class extends Tags
        {
            public static $handle = 'tag-articles';

            public function index()
            {
                return [
                    ['title' => 'Article 1'],
                    ['title' => 'Article 2'],
                    ['title' => 'Article 3'],
                ];
            }
        })::register();

        $template = <<<'EOT'
{{ my_results = {
    use_pages ? {
        tag-pages
    } : {
        tag-articles
    }
} }}{{ title }}{{ /my_results }}
EOT;

        $this->assertSame('Page 1Page 2Page 3', $this->renderString($template, ['use_pages' => true], true));
        $this->assertSame('Article 1Article 2Article 3', $this->renderString($template, ['use_pages' => false], true));
    }

    public function test_deferred_expressions_are_evaluated_correctly_from_ternary_conditions_tight()
    {
        (new class extends Tags
        {
            public static $handle = 'tag-pages';

            public function index()
            {
                return [
                    ['title' => 'Page 1'],
                    ['title' => 'Page 2'],
                    ['title' => 'Page 3'],
                ];
            }
        })::register();

        (new class extends Tags
        {
            public static $handle = 'tag-articles';

            public function index()
            {
                return [
                    ['title' => 'Article 1'],
                    ['title' => 'Article 2'],
                    ['title' => 'Article 3'],
                ];
            }
        })::register();

        $template = <<<'EOT'
{{ my_results = {use_pages ? {tag-pages} : {tag-articles}}}}{{ title }}{{ /my_results }}
EOT;

        $this->assertSame('Page 1Page 2Page 3', $this->renderString($template, ['use_pages' => true], true));
        $this->assertSame('Article 1Article 2Article 3', $this->renderString($template, ['use_pages' => false], true));
    }

    public function test_modifier_chains_break_on_equality_comparison_operator()
    {
        $template = <<<'EOT'
{{ if global:notification && global:notification|strip_tags|is_empty == false }}Not Empty{{ else}}Empty{{ /if }}
EOT;

        $data = [
            'global' => [
                'notification' => '<p></p>',
            ],
        ];

        $this->assertSame('Empty', $this->renderString($template, $data, true));

        $data = [
            'global' => [
                'notification' => '<p>Hello, world.</p>',
            ],
        ];

        $this->assertSame('Not Empty', $this->renderString($template, $data, true));
    }

    public function test_empty_terms_collection_is_falsey()
    {
        $terms = new TermCollection();
        $value = new Value($terms);

        $template = '{{ if topics }}yes{{ else }}no{{ /if }}';
        $this->assertSame('no', $this->renderString($template, [
            'topics' => $value,
        ]));
    }

    public function test_values_are_resolved_in_conditions()
    {
        $fieldType = new Select();

        // Values are different from handle here to ensure that it returns the value
        // and not the name of the variable, and to ensure it's not the handle.
        $visual = new Value('visual-value', 'visual', $fieldType);
        $semantic = new Value('semantic-value', 'semantic', $fieldType);

        $data = [
            'semantic' => $semantic,
            'visual' => $visual,
        ];

        VarTest::register();

        $template = <<<'EOT'
{{ var_test variable="{ visual == 'visual-value' ? visual : semantic }" }}
EOT;

        $this->renderString($template, $data, true);
        $this->assertSame('visual-value', VarTest::$var);

        $template = <<<'EOT'
{{ var_test variable="{ visual == 'not_visual' ? visual : semantic }" }}
EOT;
        $this->renderString($template, $data, true);
        $this->assertSame('semantic-value', VarTest::$var);

        // This, by contrast, should respect the value objects.
        $template = <<<'EOT'
{{ var_test :variable="visual == 'not_visual' ? visual : semantic" }}
EOT;
        $this->renderString($template, $data, true);
        $this->assertInstanceOf(LabeledValue::class, VarTest::$var);
        $this->assertSame('semantic-value', (string) VarTest::$var);

        $template = <<<'EOT'
{{ var_test :variable="visual == 'visual-value' ? visual : semantic" }}
EOT;
        $this->renderString($template, $data, true);
        $this->assertInstanceOf(LabeledValue::class, VarTest::$var);
        $this->assertSame('visual-value', (string) VarTest::$var);
    }
}
