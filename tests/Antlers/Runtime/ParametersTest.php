<?php

namespace Tests\Antlers\Runtime;

use Carbon\Carbon;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Group;
use Statamic\Tags\Tags;
use Tests\Antlers\Fixtures\Addon\Tags\EchoMethod;
use Tests\Antlers\Fixtures\Addon\Tags\TestTags as Test;
use Tests\Antlers\Fixtures\MethodClasses\ClassTwo;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;

class ParametersTest extends ParserTestCase
{
    use FakesViews;

    public function test_using_interpolations_with_variable_reference_resolves_correctly()
    {
        Test::register();

        $data = [
            'size_small' => 'Value one',
            'size_large' => 'Value two',
        ];

        $template = <<<'EOT'
{{ test :variable="size_{size}" }}
EOT;

        $this->assertSame('Value one', $this->renderString($template, array_merge(
            $data, ['size' => 'small']
        ), true));

        $this->assertSame('Value two', $this->renderString($template, array_merge(
            $data, ['size' => 'large']
        ), true));

        $this->assertSame('', $this->renderString($template, array_merge(
            $data, ['size' => 'medium']
        ), true));
    }

    public function test_modifier_syntax_within_variable_references_works_when_using_tags()
    {
        Test::register();

        $data = [
            'name' => 'hello',
        ];

        $template = <<<'EOT'
{{ test :variable="name|upper" }}
EOT;

        $this->assertSame('HELLO', $this->renderString($template, $data, true));
    }

    public function test_complex_expressions_are_parsed_when_using_variable_references()
    {
        Test::register();

        $data = [
            'name' => 'hello',
        ];

        $template = <<<'EOT'
{{ test :variable="null or name|upper" }}
EOT;

        $this->assertSame('HELLO', $this->renderString($template, $data, true));

        $template = <<<'EOT'
{{ test :variable="null ?? name|upper" }}
EOT;

        $this->assertSame('HELLO', $this->renderString($template, $data, true));

        $template = <<<'EOT'
{{ test :variable="true || false ?= name|upper" }}
EOT;

        $this->assertSame('HELLO', $this->renderString($template, $data, true));
    }

    public function test_ridiculous_variable_binding_expressions()
    {
        Test::register();
        $instance = new ClassTwo('Test');
        $data = [
            'form' => $instance,
        ];

        // Note to source divers: This horrible whitespace was intentional to stress the parsers.
        // Please do not do this, and please do not write variable bindings like this :)
        $template = <<<'EOT'
{{# Because someone will try and set fire to everything. #}}
  {{ test :variable="null or (null or (null or
(null or (null or (null
 or (null or (  null              or (null or
(null or (

    null      or  (     null or (null or
    
(null or (null or (


    (true == false) ? null : form

)
    )  )
  )  )  )  )    )
    )   )             )
)
)  )
)" }}
EOT;

        $this->renderString($template, $data, true);
        $this->assertSame($instance, Test::$lastValue);
    }

    public function test_interpolations_can_be_used_as_part_of_a_tag_method()
    {
        EchoMethod::register();

        $template = <<<'EOT'
{{ echo_method:{{ var_name }} }}
EOT;

        $this->assertSame('hello_world', $this->renderString($template, [
            'var_name' => 'hello_world',
        ], true));

        $template = <<<'EOT'
{{ echo_method:components/{var_name}/{{ var_name}}/{2+5+10}/more/{var_name|upper} }}
EOT;

        $this->assertSame('components/button/button/17/more/BUTTON', $this->renderString($template, [
            'var_name' => 'button',
        ], true));
    }

    public function test_array_syntax_modifiers_work_on_multi_part_variable_paths()
    {
        $data = [
            'one' => [
                'two' => Carbon::parse('October 1st, 2012'),
            ],
        ];

        $this->assertSame('2012-10-01', $this->renderString('{{ one:two format="Y-m-d" }}', $data, true));
    }

    public function test_braces_can_be_escaped_inside_parameters()
    {
        Test::register();
        $template = <<<'EOT'
{{ test variable="@{@{ hello world @}@}" }}
EOT;

        $this->assertSame('{{ hello world }}', $this->renderString($template, [], true));

        $template = <<<'EOT'
{{ test variable="@{@{ hello @{{title}@} @}@}" }}
EOT;

        $this->assertSame('{{ hello {world} }}', $this->renderString($template, ['title' => 'world'], true));
    }

    public function test_tags_are_invoked_within_interpolated_contexts()
    {
        (new class extends Tags
        {
            public static $handle = 'test';

            public function __call($method, $args)
            {
                return '<'.$method.'>';
            }
        })::register();

        (new class extends Tags
        {
            public static $handle = 'anothertag';

            public function index()
            {
                $src = $this->params->get('src');

                return 'The source is: '.$src;
            }
        })::register();

        $template = <<<'EOT'
{{ anothertag src="{test:anything}" }}
EOT;
        $this->assertSame('The source is: <anything>', $this->renderString($template, [], true));
    }

    public function test_tags_are_invoked_within_interpolated_contexts_and_conflicting_string_variable()
    {
        (new class extends Tags
        {
            public static $handle = 'test';

            public function __call($method, $args)
            {
                return '<'.$method.'>';
            }
        })::register();

        (new class extends Tags
        {
            public static $handle = 'anothertag';

            public function index()
            {
                $src = $this->params->get('src');

                return 'The source is: '.$src;
            }
        })::register();

        $template = <<<'EOT'
{{ anothertag src="{%test:anything}/{test:anything}/{test:anything}/{%test:anything}" }}
EOT;

        // This inversion of logic is to keep the behavior of "{collection:handle}"/etc. consistent in parameters.
        $this->assertSame(
            'The source is: <anything>/string var/string var/<anything>',
            $this->renderString($template, ['test' => ['anything' => 'string var']], true)
        );
    }

    public function test_interpolations_with_parameters_are_cast_to_strings()
    {
        $now = Carbon::now();
        $start = $now->year - 5;

        $data = [
            'start' => $start,
            'date_value' => $now,
        ];

        $template = <<<'EOT'
{{ loop :from="start" to="{ date_value format='Y' }" }}{{ value }}{{ /loop }}
EOT;

        $expected = implode('', range($start, $now->year));

        $this->assertSame($expected, $this->renderString($template, $data, true));
    }

    public function test_interpolation_with_array_style_parameters_returns_arrays()
    {
        $data = [
            'data' => [
                'one' => 'One',
                'two' => 'Two',
                'three' => 'Three',
            ],
        ];

        $template = <<<'EOT'
{{# Not so nice. #}}{{ foreach array="{ data limit="2" reverse="true" }" }}<{{key}}><{{ value }}>{{ /foreach }}
{{# Nicer #}}{{ foreach :array="data | limit(2) | reverse" }}<{{key}}><{{ value }}>{{ /foreach }}
EOT;

        $expected = <<<'EOT'
<two><Two><one><One>
<two><Two><one><One>
EOT;

        $this->assertSame($expected, $this->renderString($template, $data, true));
    }

    public function test_parameters_with_numeric_variables()
    {
        $template = <<<'EOT'
{{ partial:button :the_button_text="404:button_text" }}
EOT;

        $this->withFakeViews();
        $this->viewShouldReturnRaw('button', '<{{ the_button_text }}>');

        $this->assertSame('<The Button Text>', $this->renderString($template, [
            404 => [
                'button_text' => 'The Button Text',
            ],
        ]));
    }

    public function test_single_array_values_inside_parameters()
    {
        (new class extends Tags
        {
            protected static $handle = 'test';

            public function index()
            {
                return $this->params->get('param');
            }
        })::register();

        $template = <<<'EOT'
{{ test :param="[123]" }}{{ index}}:{{ value }}{{ /test }}
EOT;

        $this->assertSame('0:123', $this->renderString($template, [], true));
    }

    public function test_short_tag_parameters_do_not_cause_collisions()
    {
        (new class extends Tags
        {
            protected static $handle = 'test';

            public function index()
            {
                return $this->params->get('param').':'.$this->params->get('param_two');
            }
        })::register();

        $template = <<<'EOT'
{{ test param="{id}" param_two="{other:id}" }}
EOT;

        $result = $this->renderString($template, [
            'id' => '123',
            'other' => [
                'id' => '456',
            ],
        ], true);

        $this->assertSame('123:456', $result);
    }

    public function test_shorthand_parameter_variable_syntax()
    {
        $data = [
            'name' => 'Bacon',
        ];

        (new class extends Tags
        {
            protected static $handle = 'test';

            public function index()
            {
                return 'From the tag! '.$this->params->get('name');
            }
        })::register();

        $template = <<<'EOT'
{{ test :$name }}
EOT;

        $this->assertSame('From the tag! Bacon', $this->renderString($template, $data, true));
    }

    public function test_numeric_literals_inside_variable_bindings_stay_numbers()
    {
        (new class extends Tags
        {
            protected static $handle = 'test';

            public function index()
            {
                return $this->parse(array_merge($this->params->all(), $this->context->all()));
            }
        })::register();

        $this->assertSame(
            '124',
            $this->renderString('{{ test :my_value="123" }}{{ my_value + 1 }}{{ /test }}', [], true),
            'Simple addition'
        );

        $this->assertSame(
            '-122',
            $this->renderString('{{ test :my_value="-123" }}{{ my_value + 1 }}{{ /test }}', [], true),
            'Negative numbers'
        );

        $this->assertSame(
            '123.5',
            $this->renderString('{{ test :my_value="123.0" }}{{ my_value + 0.5 }}{{ /test }}', [], true),
            'Floats'
        );
    }

    public function test_values_objects_are_resolved_when_processing_tag_params()
    {
        (new class extends Tags
        {
            protected static $handle = 'test';

            public function index()
            {
                return $this->params->get('param');
            }
        })::register();

        $theGroup = new Group();

        $theField = new Field('the_group', [
            'type' => 'group',
            'fields' => [
                ['handle' => 'one', 'field' => ['type' => 'text']],
                ['handle' => 'two', 'field' => ['type' => 'text']],
            ],
        ]);

        $theGroup->setField($theField);

        $theValue = new Value([
            'one' => 'One',
            'two' => 'Two',
        ], 'the_group', $theGroup);

        $data = [
            'thing' => [
                [
                    'the_group' => $theValue,
                ],
            ],
        ];

        $template = <<<'EOT'
{{ thing scope="block" }}
{{ test :param="block:the_group:two" }}
{{ /thing }}
EOT;

        $this->assertSame('Two', trim($this->renderString($template, $data, true)));

        // Test when the group is the first part of the path.
        $data = [
            'the_group' => $theValue,
        ];

        $template = <<<'EOT'
{{ test :param="the_group:one" }}
EOT;

        $this->assertSame('One', trim($this->renderString($template, $data, true)));
    }
}
