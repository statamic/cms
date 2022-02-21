<?php

namespace Tests\Antlers\Runtime;

use Statamic\View\Antlers\Language\Exceptions\AntlersException;
use Tests\Antlers\ParserTestCase;

class WhereOperatorTest extends ParserTestCase
{
    protected $products = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->products = [
            'products' => [
                [
                    'name' => 'Desk',
                    'id' => 1,
                ],
                [
                    'name' => 'Lamp',
                    'id' => 2,
                ],
                [
                    'name' => 'Watch',
                    'id' => 3,
                ],
                [
                    'name' => 'Desk',
                    'id' => 4,
                ],
            ],
        ];
    }

    public function test_operator_with_simple_conditions()
    {
        $template = <<<'EOT'
{{ filtered_products = products where (id >= 3) }}
{{ filtered_products }}{{ id }}{{ /filtered_products }}
EOT;

        $this->assertSame('34', trim($this->renderString($template, $this->products, true)));
    }

    public function test_operator_with_scope_logic_group()
    {
        $data = [
            'products' => [
                ['name' => 'Desk', 'price' => 10],
                ['name' => 'Pen', 'price' => 30],
                ['name' => 'Lamp', 'price' => 20],
            ],
            'price' => 10,
        ];

        $template = <<<'EOT'
{{ filtered_products = products where (x => x:price > price) }}
{{ filtered_products }}{{ name }}{{ /filtered_products }}
EOT;
        $this->assertSame('PenLamp', trim($this->renderString($template, $data)));

        $template = <<<'EOT'
{{ filtered_products = products where (x => x.price > price) }}
{{ filtered_products }}{{ name }}{{ /filtered_products }}
EOT;
        $this->assertSame('PenLamp', trim($this->renderString($template, $data)));
    }

    public function test_where_operator_works_with_modifiers()
    {
        // Important: Nested modifier syntax MUST be enclosed in a T_LOGIC_GROUP
        //            otherwise there would be ambiguity with the right side.
        $template = <<<'EOT'
{{ filtered_products = products where ((name|lower) == 'desk') }}
{{ filtered_products }}{{ id }}{{ /filtered_products }}
EOT;

        $this->assertSame('14', trim($this->renderString($template, $this->products, true)));
    }

    public function test_where_operator_accepts_more_complex_predicates()
    {
        $template = <<<'EOT'
{{ filtered_products = products where (id >= 3 || name == 'Lamp') }}
{{ filtered_products }}{{ id }}{{ /filtered_products }}
EOT;

        $this->assertSame('234', trim($this->renderString($template, $this->products, true)));
    }

    public function test_iteration_can_happen_without_requiring_explicit_assignment()
    {
        $template = <<<'EOT'
{{ filtered_products = products where (id >= 3 || name == 'Lamp') }}{{ id }}{{ /filtered_products }}
EOT;

        $this->assertSame('234', trim($this->renderString($template, $this->products, true)));
    }

    public function test_where_and_pluck()
    {
        $template = <<<'EOT'
{{ filtered_products = products where (id >= 2 || name == 'Lamp') pluck 'name' }}{{ value }}{{ /filtered_products }}
EOT;

        $this->assertSame('LampWatchDesk', trim($this->renderString($template, $this->products, true)));
    }

    public function test_whitespace_doesnt_confuse_chained_operators()
    {
        $template = <<<'EOT'
{{ filtered_products = products where (id >= 2 || name == 'Lamp')
                                pluck 'name' }}{{ value }}{{ /filtered_products }}
EOT;

        $this->assertSame('LampWatchDesk', trim($this->renderString($template, $this->products, true)));
    }

    public function test_complex_expressions_with_attempted_loop_throws_exception()
    {
        $this->expectException(AntlersException::class);
        $template = <<<'EOT'
{{ filtered_products = products where (id >= 3 || name == 'Lamp'); shouldError = true }}{{ id }}{{ /filtered_products }}
EOT;

        $this->assertSame('234', trim($this->renderString($template, $this->products, true)));
    }
}
