<?php

namespace Tests\Antlers\Runtime;

use Carbon\Carbon;
use Tests\Antlers\Fixtures\Addon\Tags\EchoMethod;
use Tests\Antlers\Fixtures\Addon\Tags\Test;
use Tests\Antlers\Fixtures\MethodClasses\ClassTwo;
use Tests\Antlers\ParserTestCase;

class ParametersTest extends ParserTestCase
{
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
}
