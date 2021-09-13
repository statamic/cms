<?php

namespace Tests\Antlers\Runtime;

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

        $this->assertTrue($this->evaluateRaw('arr.isArray(arr(1,2,3))'));
        $this->assertEquals(3, $this->evaluateRaw('arr.count(arr(123,3,42))'));
        $this->assertEquals('3', $this->renderString('{{ arr(1,2,3) | length  }}'));
    }

    public function test_associative_arrays()
    {
        $result = $this->evaluateRaw('arr("one" => 1, "two" => 2, "three" => 3)');
        $this->assertSame(['one' => 1, 'two' => 2, 'three' => 3], $result);
    }

    public function test_dangling_element_is_allowed()
    {
        $this->assertSame(2, $this->evaluateRaw('arr.count(arr(1,2,))'));
        $this->assertSame(3, $this->evaluateRaw('arr(1,2,3,) | length'));
    }

    public function test_array_push_against_created_array()
    {
        $template = <<<'EOT'
{{  my_array = arr('one', 'two');
    arr.push(my_array, 'three');
    my_array = arr.reverse(my_array);
}}

{{ my_array }}{{ value }}{{ /my_array}}
EOT;

        $this->assertSame('threetwoone', trim($this->renderString($template)));

        // The final statement is implicitly "returned".
        $template = <<<'EOT'
{{  my_array = arr('one', 'two');
    arr.push(my_array, 'three');
    my_array = arr.reverse(my_array);
    arr.implode("", my_array);
}}
EOT;

        $this->assertSame('threetwoone', $this->renderString($template));

        $template = <<<'EOT'
{{  my_array = arr('one', 'two');
    arr.push(my_array, 'three');
    arr.implode("", (my_array|reverse));
}}
EOT;

        $this->assertSame('threetwoone', $this->renderString($template));

        $template = <<<'EOT'
{{  my_array    =
 arr   (    'one'   ,     'two'  ) ;
    arr.push  (         my_array   ,    'three'    )  ;
    arr.implode   (""    , (my_array              |   reverse));
}}
EOT;

        $this->assertSame('threetwoone', $this->renderString($template));
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
}
