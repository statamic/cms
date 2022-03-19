<?php

namespace Tests\Antlers\Runtime;

use Tests\Antlers\ParserTestCase;

class OrderbyOperatorTest extends ParserTestCase
{
    public function test_orderby_column_can_be_resolved_from_context()
    {
        $data = [
            'products' => [
                ['name' => 'Desk', 'price' => 10],
                ['name' => 'Pen', 'price' => 20],
                ['name' => 'Lamp', 'price' => 30],
            ],
            'column' => 'price',
        ];

        $this->assertSame('DeskPenLamp', $this->renderString("{{ all = products orderby (column 'asc') }}{{ name }}{{ /all }}", $data));

        $data['column'] = 'name';
        $this->assertSame('DeskLampPen', $this->renderString("{{ all = products orderby (column 'asc') }}{{ name }}{{ /all }}", $data));
    }

    public function test_orderby_can_be_resolved_from_logic_group()
    {
        $data = [
            'products' => [
                ['name' => 'Desk', 'price' => 10],
                ['name' => 'Pen', 'price' => 20],
                ['name' => 'Lamp', 'price' => 30],
            ],
        ];

        $this->assertSame('DeskPenLamp', $this->renderString("{{ all = products orderby ( ((true) ? price : name) 'asc') }}{{ name }}{{ /all }}", $data));
        $this->assertSame('DeskLampPen', $this->renderString("{{ all = products orderby ( ((false) ? price : name) 'asc') }}{{ name }}{{ /all }}", $data));
    }

    public function test_orderby_can_be_resolved_from_scoped_logic_group()
    {
        $data = [
            'products' => [
                ['name' => 'Desk', 'price' => 10],
                ['name' => 'Pen', 'price' => 20],
                ['name' => 'Lamp', 'price' => 30],
            ],
        ];

        $this->assertSame('DeskPenLamp', $this->renderString("{{ all = products orderby ( (x => x:price) 'asc') }}{{ name }}{{ /all }}", $data));
        $this->assertSame('DeskLampPen', $this->renderString("{{ all = products orderby ( (x => x:name) 'asc') }}{{ name }}{{ /all }}", $data));

        $this->assertSame('DeskPenLamp', $this->renderString("{{ all = products orderby ( (x => x.price) 'asc') }}{{ name }}{{ /all }}", $data));
        $this->assertSame('DeskLampPen', $this->renderString("{{ all = products orderby ( (x => x.name) 'asc') }}{{ name }}{{ /all }}", $data));
    }

    public function test_orders_with_modifiers_against_array_types()
    {
        $data = [
            'products' => [
                ['name' => 'Desk', 'price' => 10, 'values' => [1, 3, 3]],
                ['name' => 'Pen', 'price' => 05, 'values' => [1, 2]],
                ['name' => 'Pencil', 'price' => 44, 'values' => [1, 2, 3, 4, 5]],
                ['name' => 'Lamp', 'price' => 30, 'values' => [1]],
            ],
        ];

        $template = <<<'EOT'
{{ ordered = products orderby ((values|length) 'asc' ) }}{{ name }}{{ /ordered}}
EOT;

        $this->assertSame('LampPenDeskPencil', $this->renderString($template, $data));
    }

    public function test_orderby_operator()
    {
        $data = [
            'products' => [
                ['name' => 'Desk', 'price' => 10],
                ['name' => 'Pen', 'price' => 30],
                ['name' => 'Lamp', 'price' => 20],
            ],
        ];

        $template = <<<'EOT'
{{ ordered = products orderby (name 'asc') }}{{ name }}{{ /ordered}}
EOT;

        $this->assertSame('DeskLampPen', $this->renderString($template, $data));

        $template = <<<'EOT'
{{ ordered = products orderby (name 'desc') }}{{ name }}{{ /ordered}}
EOT;
        $this->assertSame('PenLampDesk', $this->renderString($template, $data));

        $template = <<<'EOT'
{{ ordered = products orderby (price 'desc') }}{{ name }}{{ /ordered}}
EOT;
        $this->assertSame('PenLampDesk', $this->renderString($template, $data));

        $template = <<<'EOT'
{{ ordered = products orderby (price 'desc') take 1 }}{{ name }}{{ /ordered}}
EOT;
        $this->assertSame('Pen', $this->renderString($template, $data));
    }

    public function test_order_by_can_come_from_string_or_logic_group()
    {
        $data = [
            'products' => [
                ['name' => 'Desk', 'price' => 5],
                ['name' => 'Pen', 'price' => 3],
                ['name' => 'Lamp', 'price' => 1],
            ],
        ];

        $template = <<<'EOT'
{{ ordered = products orderby (name 'asc') }}{{ name }}{{ /ordered }}
EOT;

        $this->assertSame('DeskLampPen', $this->renderString($template, $data));

        $template = <<<'EOT'
{{ test = products orderby ((false ? price : name) 'asc') }}{{ name }}{{ /test }}
EOT;
        $this->assertSame('DeskLampPen', $this->renderString($template, $data));

        $template = <<<'EOT'
{{ test = products orderby ((true ? price : name) 'asc') }}{{ name }}{{ /test }}
EOT;
        $this->assertSame('LampPenDesk', $this->renderString($template, $data));
    }

    public function test_orderby_can_evaluate_expressions_from_context_data()
    {
        $data = [
            'products' => [
                ['name' => 'Desk', 'price' => 10],
                ['name' => 'Pen', 'price' => 30],
                ['name' => 'Lamp', 'price' => 20],
            ],
            'sortAsc' => true,
        ];

        $template = <<<'EOT'
{{ ordered = products orderby (name sortAsc) }}{{ name }}{{ /ordered }}
EOT;
        $this->assertSame('DeskLampPen', $this->renderString($template, $data));

        $data['sortAsc'] = false;

        $this->assertSame('PenLampDesk', $this->renderString($template, $data));
    }

    public function test_orderby_can_evaluate_direction_from_sub_expression()
    {
        $data = [
            'products' => [
                ['name' => 'Desk', 'price' => 10],
                ['name' => 'Pen', 'price' => 30],
                ['name' => 'Lamp', 'price' => 20],
            ],
            'sortAsc' => true,
        ];

        $template = <<<'EOT'
{{ ordered = products orderby (name ((sortAsc) ? 'asc' : 'desc')) }}{{ name }}{{ /ordered}}
EOT;

        $this->assertSame('DeskLampPen', $this->renderString($template, $data));

        $data['sortAsc'] = false;

        $this->assertSame('PenLampDesk', $this->renderString($template, $data));
    }

    public function test_orderby_multiple_sort_directions()
    {
        $data = [
            'products' => [
                ['name' => 'Desk', 'price' => 10],
                ['name' => 'Pen', 'price' => 30],
                ['name' => 'Lamp', 'price' => 20],
                ['name' => 'Lamp', 'price' => 30],
            ],
        ];

        $template = <<<'EOT'
{{ ordered = products where (price >= 20)
                      orderby (name 'asc', price 'desc')
                      }}{{ name }}{{ price }}{{ /ordered}}
EOT;

        $this->assertSame('Lamp30Lamp20Pen30', $this->renderString($template, $data));
    }

    public function test_orderby_multiple_sort_directions_from_scoped_logic_groups()
    {
        $data = [
            'products' => [
                ['name' => 'Desk', 'price' => 10],
                ['name' => 'Pen', 'price' => 30],
                ['name' => 'Lamp', 'price' => 20],
                ['name' => 'Lamp', 'price' => 30],
            ],
        ];

        $template = <<<'EOT'
{{ ordered = products where (price >= 20)
                      orderby ((x => x:name) 'asc', (y => y:price) 'desc')
                      }}{{ name }}{{ price }}{{ /ordered}}
EOT;

        $this->assertSame('Lamp30Lamp20Pen30', $this->renderString($template, $data));

        $template = <<<'EOT'
{{ ordered = products where (price >= 20)
                      orderby ((x => x.name) 'asc', (y => y:price) 'desc')
                      }}{{ name }}{{ price }}{{ /ordered}}
EOT;

        $this->assertSame('Lamp30Lamp20Pen30', $this->renderString($template, $data));

        $template = <<<'EOT'
{{ ordered = products where (price >= 20)
                      orderby ((x => x.name) 'asc', (y => y.price) 'desc')
                      }}{{ name }}{{ price }}{{ /ordered}}
EOT;

        $this->assertSame('Lamp30Lamp20Pen30', $this->renderString($template, $data));
    }

    public function test_orderby_multiple_sort_directions_from_scoped_logic_groups_and_var_references()
    {
        $data = [
            'products' => [
                ['name' => 'Desk', 'price' => 10],
                ['name' => 'Pen', 'price' => 30],
                ['name' => 'Lamp', 'price' => 20],
                ['name' => 'Lamp', 'price' => 30],
            ],
        ];

        $template = <<<'EOT'
{{ ordered = products where (price >= 20)
                      orderby ((x => x:name) 'asc', price 'desc')
                      }}{{ name }}{{ price }}{{ /ordered}}
EOT;

        $this->assertSame('Lamp30Lamp20Pen30', $this->renderString($template, $data));

        $template = <<<'EOT'
{{ ordered = products where (price >= 20)
                      orderby ((x => x.name) 'asc', price 'desc')
                      }}{{ name }}{{ price }}{{ /ordered}}
EOT;

        $this->assertSame('Lamp30Lamp20Pen30', $this->renderString($template, $data));
    }

    public function test_orderby_with_strings_sorts_array_if_matching_data_property()
    {
        $data = [
            'products' => [
                ['name' => 'Desk', 'price' => 10],
                ['name' => 'Pen', 'price' => 30],
                ['name' => 'Lamp', 'price' => 20],
                ['name' => 'Lamp', 'price' => 30],
            ],
        ];

        $template = <<<'EOT'
{{ ordered = products where (price >= 20)
                      orderby ('name' 'asc', 'price' 'desc')
                      }}{{ name }}{{ price }}{{ /ordered}}
EOT;

        $this->assertSame('Lamp30Lamp20Pen30', $this->renderString($template, $data));
    }

    public function test_orderby_can_accept_modifiers()
    {
        $data = [
            'products' => [
                ['name' => 'Desk', 'price' => 10],
                ['name' => 'Pen', 'price' => 20],
                ['name' => 'Lamp', 'price' => 30],
            ],
        ];

        $this->assertSame('PenDeskLamp', $this->renderString("{{ all = products orderby ( (name|length) 'asc') }}{{ name }}{{ /all }}", $data, true));
        $this->assertSame('DeskLampPen', $this->renderString("{{ all = products orderby ( (name|length) 'desc') }}{{ name }}{{ /all }}", $data, true));
        $this->assertSame('LampDeskPen', $this->renderString("{{ all = products orderby ( (name|length) 'desc', name 'desc') }}{{ name }}{{ /all }}", $data, true));
    }

    public function test_orderby_with_other_operators()
    {
        $data = [
            'products' => [
                ['name' => 'Desk', 'price' => 10],
                ['name' => 'Pen', 'price' => 30],
                ['name' => 'Lamp', 'price' => 20],
            ],
        ];

        $template = <<<'EOT'
{{ ordered = products where (price >= 20)
                      orderby (name 'desc')
                      take 1 }}{{ name }}{{ /ordered}}
EOT;

        $this->assertSame('Pen', $this->renderString($template, $data));

        $template = <<<'EOT'
{{ ordered = products where (price >= 20)
                      orderby (name 'asc')
                      pluck 'name'
                      take 1 }}{{ value }}{{ /ordered}}
EOT;
        $this->assertSame('Lamp', $this->renderString($template, $data));

        $template = <<<'EOT'
{{ ordered = products where (price >= 20)
                      orderby (name 'asc')
                      pluck 'name'
                      take 3 }}{{ value }}{{ /ordered}}
EOT;
        $this->assertSame('LampPen', $this->renderString($template, $data));
    }
}
