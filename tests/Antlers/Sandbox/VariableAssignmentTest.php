<?php

namespace Tests\Antlers\Sandbox;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Collection;
use Statamic\Facades\Taxonomy;
use Tests\Antlers\ParserTestCase;
use Tests\PreventSavingStacheItemsToDisk;

class VariableAssignmentTest extends ParserTestCase
{
    use PreventSavingStacheItemsToDisk;

    public function test_simple_variable_is_set()
    {
        $result = $this->evaluate('my_variable = 1; another_var = 3;', []);

        $this->assertArrayHasKey('my_variable', $result);
        $this->assertArrayHasKey('another_var', $result);

        $this->assertEquals(1, $result['my_variable']);
        $this->assertEquals(3, $result['another_var']);

        $resultTwo = $this->evaluate('my_variable += 30;', $result);

        $this->assertEquals(31, $resultTwo['my_variable']);
        $this->assertEquals(3, $resultTwo['another_var']);
    }

    protected function assertHasVariableWithValue($variable, $value, $text)
    {
        $result = $this->evaluate($text);
        $this->assertArrayHasKey($variable, $result);

        $this->assertEquals($value, $result[$variable]);
    }

    public function test_addition_assignment()
    {
        $this->assertHasVariableWithValue(
            'test', 30, 'test = 25; test += 5;'
        );
    }

    public function test_division_assignment()
    {
        $this->assertHasVariableWithValue(
            'test', 5, 'test = 25; test /= 5;'
        );
    }

    public function test_modulus_assignment()
    {
        $this->assertHasVariableWithValue(
            'test', 0, 'test = 25; test %= 5;'
        );
    }

    public function test_multiplication_assignment()
    {
        $this->assertHasVariableWithValue(
            'test', 25, 'test = 5; test *= 5;'
        );
    }

    public function test_subtraction_assignment()
    {
        $this->assertHasVariableWithValue(
            'test', 20, 'test = 25; test -= 5;'
        );
    }

    public function test_simple_assignment_within_tag_contexts()
    {
        $template = <<<'EOT'
{{ myvar = 0 }}BEFORE{{ myvar }}|{{ loop from="1" to="10" }}{{ myvar += 1; myvar }}|{{ /loop }}AFTER{{ myvar }}
EOT;
        $expected = 'BEFORE0|1|2|3|4|5|6|7|8|9|10|AFTER10';

        $this->assertSame($expected, $this->renderString($template, [], true));
    }

    public function test_nested_tags_process_shared_assignment_data()
    {
        $template = <<<'EOT'
{{ myvar = 0 }}BEFORE{{ myvar }}|{{ loop from="1" to="10" }}{{ myvar += 1; }}{{ loop from="1" to="10" }}{{ myvar += 1; myvar }}{{ /loop }}|{{ /loop }}AFTER{{ myvar }}
EOT;

        $expected = 'BEFORE0|234567891011|13141516171819202122|24252627282930313233|35363738394041424344|46474849505152535455|57585960616263646566|68697071727374757677|79808182838485868788|90919293949596979899|101102103104105106107108109110|AFTER110';

        $this->assertSame($expected, $this->renderString($template, [], true));
    }

    public function test_assignments_are_processed_while_iterating()
    {
        $data = [
            'products' => [
                ['id' => 1, 'name' => 'Desk', 'category' => 'Office', 'sale' => true],
                ['id' => 2, 'name' => 'Plant', 'category' => 'Home', 'sale' => true],
                ['id' => 3, 'name' => 'Stapler', 'category' => 'Office', 'sale' => true],
                ['id' => 4, 'name' => 'Pen', 'category' => 'Office', 'sale' => false],
                ['id' => 5, 'name' => 'Table', 'category' => 'Home', 'sale' => false],
            ],
        ];

        $template = <<<'EOT'
{{ my_var = 0; }}{{ products }}{{ my_var += 1; }}{{ /products }}{{ my_var }}
EOT;
        $this->assertSame('5', $this->renderString($template, $data));
    }

    public function test_assignments_within_another_scope_do_not_leak_to_outer_scope()
    {
        $data = [
            'products' => [
                ['id' => 1, 'name' => 'Desk', 'category' => 'Office', 'sale' => true],
                ['id' => 2, 'name' => 'Plant', 'category' => 'Home', 'sale' => true],
                ['id' => 3, 'name' => 'Stapler', 'category' => 'Office', 'sale' => true],
                ['id' => 4, 'name' => 'Pen', 'category' => 'Office', 'sale' => false],
                ['id' => 5, 'name' => 'Table', 'category' => 'Home', 'sale' => false],
            ],
        ];

        $template = <<<'EOT'
{{ my_var = 0; }}{{ products }}{{ my_var += 1; }}{{ another_var += 1; another_var }}{{ /products }}<my_var {{ my_var }}><another_var: {{ another_var ?? 0 }}>
EOT;
        $this->assertSame('11111<my_var 5><another_var: 0>', $this->renderString($template, $data));
    }

    public function test_nested_arrays_can_be_summed()
    {
        Taxonomy::make('tags')->save();
        Collection::make('blog')->taxonomies(['tags'])->save();
        EntryFactory::collection('blog')->id('1')->data(['tags' => ['rad', 'test', 'test-two']])->create();
        EntryFactory::collection('blog')->id('2')->data(['tags' => ['rad', 'two']])->create();
        EntryFactory::collection('blog')->id('3')->data(['tags' => ['meh']])->create();
        EntryFactory::collection('blog')->id('4')->create();

        $template = <<<'EOT'
{{ my_count = 0 }}
{{ collection:blog }}
    {{ my_count += (tags|count) }}
{{ /collection:blog }}
{{ my_count }}
EOT;

        $this->assertSame('6', trim($this->renderString($template, [], true)));

        $template = <<<'EOT'
{{ $my_count = 0; }}
{{ collection:blog }}
    {{ $my_count += (tags|count) }}
{{ /collection:blog }}
{{ $my_count }}
EOT;

        $this->assertSame('6', trim($this->renderString($template, [], true)));
    }
}
