<?php

namespace Tests\Antlers\Runtime;

use Tests\Antlers\ParserTestCase;

class ValueInterpolationTest extends ParserTestCase
{
    public function test_interpolated_ranges_are_replaced()
    {
        $data = ['width' => 200];

        $this->assertSame('100px', $this->renderString('{{ width | /:2 }}px', $data));
        $this->assertSame('100px', $this->renderString("{{ '{width /2}px' }}", $data));
        $this->assertSame('100px', $this->renderString("{{ '{width / 2}px' }}", $data));
        $this->assertSame('100px', $this->renderString("{{ '{width/2}px' }}", $data));
        $this->assertSame('100px', $this->renderString("{{ '{width/2}' + 'px' }}", $data));
    }

    public function test_interpolation_doesnt_care_about_internal_whitespace()
    {
        $template = <<<'EOT'
{{ (true) ? {
    'hello'
 } : {
    'goodbye'
 } }}
EOT;

        $this->assertSame('hello', $this->renderString($template));

        $template = <<<'EOT'
{{ (false) ? {
    'hello'
 } : {'goodbye'} }}
EOT;

        $this->assertSame('goodbye', $this->renderString($template));
    }

    public function test_arrays_can_be_returned_from_interpolated_regions()
    {
        $data = [
            'products' => [
                ['name' => 'Desk', 'price' => 10],
                ['name' => 'Pen', 'price' => 30],
                ['name' => 'Lamp', 'price' => 20],
            ],
        ];

        $template = <<<'EOT'
{{ test = {products} }}{{ name }}{{ /test }}
EOT;

        $this->assertSame('DeskPenLamp', $this->renderString($template, $data));
    }

    public function test_actual_value_is_returned_from_interpolations_for_arguments()
    {
        $this->assertSame(3, intval($this->renderString('{{ arr.count({test}) }}', [
            'test' => ['one', 'two', 'three'],
        ])));

        $this->assertSame(4, intval($this->renderString('{{ arr.count({test}) }}', [
            'test' => ['one', 'two', 'three', 'four'],
        ])));
    }

    public function test_interpolated_results_from_complex_expressions_return_their_value()
    {
        $this->assertSame(4, intval($this->renderString('{{ arr.count({arr.explode(",", "1,2,3,4")}) }}')));
        $this->assertSame(4, intval($this->renderString('{{ {arr.count({arr.explode(",", "1,2,3,4")})} }}')));
        $this->assertSame(5, intval($this->renderString('{{ arr.count({arr.explode(",", "1,2,3,4,5")}) }}')));
        $this->assertSame(6, intval($this->renderString('{{ arr.count({arr.explode(",", "a,b,c,d,e,f")}) }}')));
        $this->assertSame(6, intval($this->renderString('{{ arr.count({arr.explode("|", "a|b|c|d|e|f")}) }}')));
        $this->assertSame(6, intval($this->renderString('{{ arr.count({{arr.explode("|", "a|b|c|d|e|f")}}) }}')));
    }
}
