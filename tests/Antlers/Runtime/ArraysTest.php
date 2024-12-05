<?php

namespace Tests\Antlers\Runtime;

use PHPUnit\Framework\Attributes\Test;
use Tests\Antlers\ParserTestCase;

class ArraysTest extends ParserTestCase
{
    public function test_array_plucking()
    {
        $data = [
            'titles' => [
                'Title One',
                'Title Two',
            ],
        ];

        $this->assertSame('Title One', $this->renderString('{{ titles:0 }}', $data));
        $this->assertSame('Title Two', $this->renderString('{{ titles:1 }}', $data));
    }

    public function test_dictionary_access()
    {
        $data = [
            'address' => [
                'city' => 'City Name',
                'region' => 'Region Name',
                'country' => 'Country Name',
            ],
        ];

        $this->assertSame('City Name', $this->renderString('{{ address:city }}', $data));
        $this->assertSame('Region Name', $this->renderString('{{ address:region }}', $data));
        $this->assertSame('Country Name', $this->renderString('{{ address:country }}', $data));
    }

    public function test_multi_dimensional_array_access()
    {
        $data = [
            'posts' => [
                ['title' => 'Title One'],
                ['title' => 'Title Two'],
            ],
        ];

        $template = '{{ posts }}{{ title }}{{ /posts }}';
        $expected = 'Title OneTitle Two';

        $this->assertSame($expected, $this->renderString($template, $data));
    }

    public function test_multi_dimensional_array_with_plucking()
    {
        $data = [
            'posts' => [
                ['title' => 'Title One'],
                ['title' => 'Title Two'],
            ],
        ];

        $template = '{{ posts:1:title }}';
        $expected = 'Title Two';

        $this->assertSame($expected, $this->renderString($template, $data));
    }

    public function test_numeric_indexes_are_valid_inside_nested_paths()
    {
        $data = [
            'posts' => [
                ['title' => 'Title One'],
                ['title' => 'Title Two'],
            ],
        ];

        $template = '{{ posts[1]title }}';
        $expected = 'Title Two';

        $this->assertSame($expected, $this->renderString($template, $data));
    }

    public function test_variable_reference_paths_resolve_at_the_end_of_a_path()
    {
        $data = [
            'posts' => [
                ['title' => 'Title One'],
                ['title' => 'Title Two'],
            ],
        ];

        $this->assertSame('Title Two', $this->renderString('{{ posts[1] }}{{ title }}{{ /posts[1]}}', $data));
        $this->assertSame('Title One', $this->renderString('{{ posts:0 }}{{ title }}{{ /posts:0 }}', $data));
        $this->assertSame('Title Two', $this->renderString('{{ posts[1] }}{{ title }}{{ /posts[1] }}', $data));
        $this->assertSame('Title One', $this->renderString('{{ posts:0 }}{{ title }}{{ /posts:0 }}', $data));
    }

    public function test_variable_paths_with_string_that_contain_whitespace()
    {
        $data = [
            'stringkey' => 'string key',
            'array_var' => [
                'string key' => 'Hello, world.',
                'stringkey' => 'Hello, wilderness.',
            ],
        ];

        $this->assertSame('Hello, world.', $this->renderString('{{ array_var["string key"] }}', $data));
        $this->assertSame('Hello, wilderness.', $this->renderString('{{ array_var["stringkey"] }}', $data));
        $this->assertSame('Hello, world.', $this->renderString('{{ array_var[stringkey] }}', $data));
    }

    public function test_variable_paths_with_escaped_strings_resolve_correctly()
    {
        $data = [
            'test' => [
                'test' => 'Hello, wilderness.',
                "test['test']" => 'Hello, world.',
            ],
        ];

        $this->assertSame('Hello, world.', $this->renderString("{{ test['test[\'test\']'] }}", $data));
        $this->assertSame('Hello, world.', $this->renderString("{{ test[\"test['test']\"] }}", $data));
        $this->assertSame('Hello, wilderness.', $this->renderString('{{ test:test }}', $data));
    }

    public function test_variable_path_with_string_escape_sequence_resolves_correctly()
    {
        $data = [
            'test' => [
                'test' => 'Hello, wilderness.',
                '\\test' => 'Hello, world.',
            ],
        ];

        $template = <<<'EOT'
{{ test['\\test'] }}
EOT;

        $this->assertSame('Hello, world.', $this->renderString($template, $data));
    }

    public function test_variable_path_with_newline_escape_sequence()
    {
        $data = [
            'test' => [
                'test' => 'Hello, wilderness.',
                "\ntest" => 'Hello, world.',
            ],
        ];

        $template = <<<'EOT'
{{ test['\ntest'] }}
EOT;

        $this->assertSame('Hello, world.', $this->renderString($template, $data));
    }

    public function test_variable_path_with_tab_escape_sequence()
    {
        $data = [
            'test' => [
                'test' => 'Hello, wilderness.',
                "\ttest" => 'Hello, world.',
            ],
        ];

        $template = <<<'EOT'
{{ test['\ttest'] }}
EOT;

        $this->assertSame('Hello, world.', $this->renderString($template, $data));
    }

    public function test_variable_path_with_carriage_return_escape_sequence()
    {
        $data = [
            'test' => [
                'test' => 'Hello, wilderness.',
                "\rtest" => 'Hello, world.',
            ],
        ];

        $template = <<<'EOT'
{{ test['\rtest'] }}
EOT;

        $this->assertSame('Hello, world.', $this->renderString($template, $data));
    }

    public function test_arr_makes_array()
    {
        $result = $this->evaluateRaw('arr(1, 2, 3)');
        $this->assertIsArray($result);
        $this->assertCount(3, $result);

        $this->assertTrue($this->evaluateRaw('arr(1,2,3) | is_array'));
        $this->assertEquals('3', $this->renderString('{{ arr(1,2,3) | length  }}'));
    }

    public function test_associative_arrays()
    {
        $result = $this->evaluateRaw('arr("one" => 1, "two" => 2, "three" => 3)');
        $this->assertSame(['one' => 1, 'two' => 2, 'three' => 3], $result);
    }

    public function test_dangling_element_is_allowed()
    {
        $this->assertSame(3, $this->evaluateRaw('arr(1,2,3,) | length'));
    }

    public function test_array_push()
    {
        $data = [
            'simple' => [
                'Alice',
                'Bob',
                'Charlie',
            ],
            'map' => [
                ['name' => 'Alice'],
                ['name' => 'Bob'],
                ['name' => 'Charlie'],
            ],
        ];

        // The evluate test method adds {{ }} already.
        $template = <<<'EOT'
simple += 'Daniel'; map += ['name' => 'Daniel']
EOT;

        $results = $this->evaluate($template, $data);

        $expectedSimple = ['Alice', 'Bob', 'Charlie', 'Daniel'];
        $expectedMap = [
            ['name' => 'Alice'],
            ['name' => 'Bob'],
            ['name' => 'Charlie'],
            ['name' => 'Daniel'],
        ];

        $this->assertSame($expectedSimple, $results['simple']);
        $this->assertSame($expectedMap, $results['map']);
    }

    public function test_nested_arrays()
    {
        $result = $this->evaluateRaw('arr("one" => 1, "two" => 2, "three" => arr(1,2,3, 4 => arr(1,2)))');

        $this->assertSame(
            [
                'one' => 1,
                'two' => 2,
                'three' => [
                    1,
                    2,
                    3,
                    4 => [
                        1,
                        2,
                    ],
                ],
            ],
            $result
        );

        $result = $this->evaluateRaw('arr(
                   "one" => 1,
                   "two" => 2,
                   "three" => arr(
                        1,
                        2,
                        3,
                        4 => arr(
                            1,
                            2
                        )
                    ))');

        $this->assertSame(
            [
                'one' => 1,
                'two' => 2,
                'three' => [
                    1,
                    2,
                    3,
                    4 => [
                        1,
                        2,
                    ],
                ],
            ],
            $result
        );
    }

    public function test_nested_arrays_using_bracket_syntax()
    {
        $result = $this->evaluateRaw('["one" => 1, "two" => 2, "three" => [1,2,3, 4 => [1,2]]]');

        $this->assertSame(
            [
                'one' => 1,
                'two' => 2,
                'three' => [
                    1,
                    2,
                    3,
                    4 => [
                        1,
                        2,
                    ],
                ],
            ],
            $result
        );

        $result = $this->evaluateRaw('[
                   "one" => 1,
                   "two" => 2,
                   "three" => [
                        1,
                        2,
                        3,
                        4 => [
                            1,
                            2
                        ]
                    ]]');

        $this->assertSame(
            [
                'one' => 1,
                'two' => 2,
                'three' => [
                    1,
                    2,
                    3,
                    4 => [
                        1,
                        2,
                    ],
                ],
            ],
            $result
        );
    }

    public function test_array_key_values()
    {
        // Note: This test is to validate the parser/runtime does not explode.
        //       Using these as array keys should be avoided, if possible.
        $data = [
            'data' => [
                null => 'Null Value',
                true => 'True Value',
                false => 'False Value',
            ],
        ];

        $template = <<<'EOT'
<{{ data:true }}><{{ data:false }}><{{ data:null }}>
EOT;

        $this->assertSame('<True Value><False Value><Null Value>', $this->renderString($template, $data));
    }

    public function test_array_shorthand_syntax_can_be_used_without_trailing_spaces()
    {
        $template = <<<'EOT'
{{ keyword1 = 'dance' }}
{{ keyword2 = 'party' }}
{{ keywords = [$keyword1, $keyword2] }}
{{ keywords }}<{{ value }}>{{ /keywords }}
EOT;

        $this->assertSame('<dance><party>', trim($this->renderString($template)));
    }

    public function test_creation_of_new_array_elements()
    {
        $template = <<<'EOT'
{{ the_array = ['One', 'Two'] }}
One: {{ the_array.0 }}
Two: {{ the_array.1 }}
{{ the_array.2 = 'Three'; }}
One: {{ the_array.0 }}
Two: {{ the_array.1 }}
Three: {{ the_array.2 }}
{{ the_array = [] }}
One: {{ the_array.0 }}
Two: {{ the_array.1 }}
Three: {{ the_array.2 }}
{{ the_array.a.deeply.nested.path = 'One!'; }}
{{ the_array.a.deeply.nested.more = 'Two!'; }}
{{ the_array.a.deeply.nested.even_more = 'Three!'; }}
One: {{ the_array.a.deeply.nested.path }}
Two: {{ the_array.a.deeply.nested.more }}
Three: {{ the_array.a.deeply.nested.even_more }}
EOT;

        $expected = <<<'EOT'
One: One
Two: Two

One: One
Two: Two
Three: Three

One: 
Two: 
Three: 



One: One!
Two: Two!
Three: Three!
EOT;

        $this->assertSame($expected, trim($this->renderString($template)));
    }

    public function test_arrays_as_the_tag_name()
    {
        $this->assertSame('array', $this->renderString('{{ [1, 2, 3, 4] | type_of }}', [], true));
    }
}
