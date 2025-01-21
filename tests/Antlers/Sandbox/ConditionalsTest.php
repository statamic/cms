<?php

namespace Tests\Antlers\Sandbox;

use Statamic\Tags\Tags;
use Tests\Antlers\ParserTestCase;

class ConditionalsTest extends ParserTestCase
{
    public function test_sandbox_will_defer_collapsing_arrays()
    {
        $data = [
            'people' => [
                ['name' => 'Charlie'],
                ['name' => 'Dave'],
                ['name' => 'Alice'],
                ['name' => 'Bob'],
            ],
            'houses' => [],
        ];

        $template = <<<'EOT'
{{ if ( (people pluck 'name')|contains('Alice') ) }}yes{{ else }}no{{ /if }}
EOT;
        $this->assertSame('yes', $this->renderString($template, $data));

        $template = <<<'EOT'
{{ if houses }}yes{{ else }}no{{ /if }}
EOT;
        $this->assertSame('no', $this->renderString($template, $data));

        $template = <<<'EOT'
{{ if people }}yes{{ else }}no{{ /if }}
EOT;

        $this->assertSame('yes', $this->renderString($template, $data));

        $template = <<<'EOT'
{{ if houses == false }}yes{{ else }}no{{ /if }}
EOT;

        $this->assertSame('yes', $this->renderString($template, $data));

        $template = <<<'EOT'
{{ if houses === false }}yes{{ else }}no{{ /if }}
EOT;

        $this->assertSame('no', $this->renderString($template, $data));
    }

    public function test_tags_are_not_initially_evaluated_twice()
    {
        $template = <<<'EOT'
{{ if {switch between='yes|no'} == 'yes' }}yes{{ else }}no{{ /if }}{{ if {switch between='yes|no'} == 'yes' }}yes{{ else }}no{{ /if }}
EOT;

        $this->assertSame('yesno', $this->renderString($template, [], true));
    }

    public function test_tags_are_evaluated_twice_if_called_twice()
    {
        $template = <<<'EOT'
{{ if {switch between='yes|no'} == 'yes' && {switch between='yes|no'} == 'no' }}yes{{ else }}no{{ /if }}{{ if {switch between='yes|no'} == 'yes' && {switch between='yes|no'} == 'no' }}yes{{ else }}no{{ /if }}{{ if {switch between='yes|no'} == 'no' && {switch between='yes|no'} == 'yes' }}yes{{ else }}no{{ /if }}
EOT;

        $this->assertSame('yesyesno', $this->renderString($template, [], true));
    }

    public function test_value_adjustments_inside_of_conditionals()
    {
        $template = <<<'EOT'
{{ if 'bob' == 'bo{c}' }}yes{{ else }}no{{ /if }}
EOT;
        $this->assertSame('yes', $this->renderString($template, ['c' => 'b'], false));
    }

    public function test_many_tag_value_adjustments_inside_of_conditionals()
    {
        $template = <<<'EOT'
{{ if 'one_{switch between='yes|no'}' == 'one_yes' && 'two_{switch between='yes|no'}' == 'two_no' }}yes{{ else }}no{{ /if }}
EOT;

        $this->assertSame('yes', $this->renderString($template, [], true));
    }

    public function test_value_adjustments_resolved_tag_values()
    {
        (new class extends Tags
        {
            protected static $handle = 'the_tag';

            public function index()
            {
                return 'bob';
            }
        })::register();

        $template = <<<'EOT'
{{ if 'bob' == '{the_tag}' }}yes{{ else }}no{{ /if }}
EOT;
        $this->assertSame('yes', $this->renderString($template, [], true));
    }

    public function test_sandbox_evaluates_simple_boolean_expressions()
    {
        $result = $this->getBoolResult('true == false', []);
        $result2 = $this->getBoolResult('true == true == false', []);
        $result3 = $this->getBoolResult('(count >= 3) && (count < 100)', [
            'count' => 3,
        ]);

        $this->assertSame(false, $result);
        $this->assertSame(false, $result2);
        $this->assertSame(true, $result3);
    }

    public function test_it_evaluates_null_coalescence_groups()
    {
        $input = 'meta_title ?? title ?? "No Title Set"';

        $result = $this->evaluateRaw($input, []);
        $this->assertSame('No Title Set', $result);

        $result = $this->evaluateRaw($input, ['meta_title' => 'Meta Title']);
        $this->assertSame('Meta Title', $result);

        $result = $this->evaluateRaw($input, ['title' => 'A Title']);
        $this->assertSame('A Title', $result);
    }

    public function test_it_evaluates_alias_null_coalescence_groups()
    {
        $input = 'meta_title ?: title ?: "No Title Set"';

        $result = $this->evaluateRaw($input, []);
        $this->assertSame('No Title Set', $result);

        $result = $this->evaluateRaw($input, ['meta_title' => 'Meta Title']);
        $this->assertSame('Meta Title', $result);

        $result = $this->evaluateRaw($input, ['title' => 'A Title']);
        $this->assertSame('A Title', $result);

        $input = 'meta_title ?? title ?: "No Title Set"';

        $result = $this->evaluateRaw($input, []);
        $this->assertSame('No Title Set', $result);

        $result = $this->evaluateRaw($input, ['meta_title' => 'Meta Title']);
        $this->assertSame('Meta Title', $result);

        $result = $this->evaluateRaw($input, ['title' => 'A Title']);
        $this->assertSame('A Title', $result);
    }

    public function test_it_evaluates_simple_ternary_groups()
    {
        $input = 'is_sold ? "sold" : "available"';
        $result = $this->evaluateRaw($input, []);
        $this->assertSame('available', $result);

        $result = $this->evaluateRaw($input, ['is_sold' => true]);
        $this->assertSame('sold', $result);

        $input = 'is_sold ? ("sold") : ("available")';
        $result = $this->evaluateRaw($input, []);
        $this->assertSame('available', $result);

        $result = $this->evaluateRaw($input, ['is_sold' => true]);
        $this->assertSame('sold', $result);
    }

    public function test_it_evaluates_nested_ternary_groups()
    {
        $input = 'is_sold ? ("sold") : (inner_val ? ("inner truth") : "available")';
        $result = $this->evaluateRaw($input, []);
        $this->assertSame('available', $result);

        $result = $this->evaluateRaw($input, ['is_sold' => true]);
        $this->assertSame('sold', $result);

        $result = $this->evaluateRaw($input, [
            'is_sold' => false,
        ]);
        $this->assertSame('available', $result);

        $result = $this->evaluateRaw($input, [
            'is_sold' => false,
            'inner_val' => true,
        ]);
        $this->assertSame('inner truth', $result);
    }

    public function test_numeric_values_inside_conditions()
    {
        $this->assertSame('Yes', $this->renderString('{{ if -1 }}Yes{{ else }}No{{ /if }}'));
        $this->assertSame('Yes', $this->renderString('{{ if 1 }}Yes{{ else }}No{{ /if }}'));
        $this->assertSame('Yes', $this->renderString('{{ if 1.0 }}Yes{{ else }}No{{ /if }}'));
        $this->assertSame('No', $this->renderString('{{ if 0 }}Yes{{ else }}No{{ /if }}'));
        $this->assertSame('No', $this->renderString('{{ if 0.0 }}Yes{{ else }}No{{ /if }}'));
    }

    public function test_interpolations_inside_conditions_are_evaluated_using_conditional_mode()
    {
        (new class extends Tags
        {
            protected static $handle = 'the_tag';

            public function wildcard($method)
            {
                return true;
            }
        })::register();

        $template = <<<'EOT'
{{ if {the_tag:has:some:nested:stuff} === true }}
Yes
{{ /if }}
EOT;

        $this->assertSame('Yes', trim($this->renderString($template, [], true)));
    }
}
