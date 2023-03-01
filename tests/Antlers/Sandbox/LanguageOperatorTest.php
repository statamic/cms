<?php

namespace Tests\Antlers\Sandbox;

use Tests\Antlers\ParserTestCase;

class LanguageOperatorTest extends ParserTestCase
{
    public function test_arr_skip()
    {
        $data = [
            'people' => [
                ['name' => 'Charlie'],
                ['name' => 'Dave'],
                ['name' => 'Alice'],
                ['name' => 'Bob'],
            ],
        ];

        $template = <<<'EOT'
{{ (people orderby (name 'asc') skip (2) take (2) pluck 'name') | sentence_list }}
EOT;

        $this->assertSame('Charlie and Dave', $this->renderString($template, $data));
    }

    public function test_modifiers_can_be_used_on_plucked_values()
    {
        $data = [
            'test' => [
                ['name' => 'Test'],
                ['name' => 'Test2'],
                ['name' => 'Test3'],
            ],
        ];

        $this->assertSame('Test, Test2, and Test3', $this->renderString('{{ (test pluck "name") | sentence_list }}', $data));
    }

    public function test_take_operator()
    {
        $data = ['test_array' => ['one', 'two', 'three', 'four']];
        $template = <<<'EOT'
{{ test = test_array take 2 }}{{ value }}{{ /test }}
EOT;

        $this->assertSame('onetwo', $this->renderString($template, $data));

        $data = ['test_array' => ['one']];
        $template = <<<'EOT'
{{ test = test_array take 2 }}{{ value }}{{ /test }}
EOT;

        $this->assertSame('one', $this->renderString($template, $data));
    }

    public function test_array_merge()
    {
        $a = [1, 2, 3];
        $b = [3, 4, 5];

        $this->assertSame('123345', $this->renderString('{{ merged = a merge b }}{{ value }}{{ /merged }}', ['a' => $a, 'b' => $b]));
    }

    public function test_pluck_on_variable_that_doesnt_exist()
    {
        $this->assertSame('', $this->renderString('{{ doesnt_exist pluck("title") }}'));
    }
}
