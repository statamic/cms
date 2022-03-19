<?php

namespace Tests\Antlers\Runtime;

use Tests\Antlers\ParserTestCase;

class ModifierEquivalenceTest extends ParserTestCase
{
    public function test_add_modifier_shorthand_works()
    {
        $data = ['value' => 5];

        $this->assertSame(10, intval($this->renderString('{{ value + 5 }}', $data)));
        $this->assertSame(10, intval($this->renderString('{{ value | add: 5 }}', $data)));
        $this->assertSame(10, intval($this->renderString('{{ value | add:{value} }}', $data)));
        $this->assertSame(10, intval($this->renderString('{{ value | add:{2 + 3} }}', $data)));
        $this->assertSame(10, intval($this->renderString('{{ value | add: {value} }}', $data)));
        $this->assertSame(10, intval($this->renderString('{{ value | add: {2 + 3} }}', $data)));
        $this->assertSame(10, intval($this->renderString('{{ value | +: 5 }}', $data)));
        $this->assertSame(10, intval($this->renderString('{{ {value + 5} }}', $data)));
    }

    public function test_subtract_modifier_shorthand_works()
    {
        $data = ['value' => 5];

        $this->assertSame(0, intval($this->renderString('{{ value - 5 }}', $data)));
        $this->assertSame(0, intval($this->renderString('{{ value | subtract: 5 }}', $data)));
        $this->assertSame(0, intval($this->renderString('{{ value | subtract:{value} }}', $data)));
        $this->assertSame(0, intval($this->renderString('{{ value | subtract:{2 + 3} }}', $data)));
        $this->assertSame(0, intval($this->renderString('{{ value | subtract: {value} }}', $data)));
        $this->assertSame(0, intval($this->renderString('{{ value | subtract: {2 + 3} }}', $data)));
        $this->assertSame(0, intval($this->renderString('{{ value | -: 5 }}', $data)));
        $this->assertSame(0, intval($this->renderString('{{ {value - 5} }}', $data)));
    }

    public function test_division_modifier_shorthand_works()
    {
        $data = ['value' => 5];

        $this->assertSame(1, intval($this->renderString('{{ value / 5 }}', $data)));
        $this->assertSame(1, intval($this->renderString('{{ value | divide: 5 }}', $data)));
        $this->assertSame(1, intval($this->renderString('{{ value | divide:{value} }}', $data)));
        $this->assertSame(1, intval($this->renderString('{{ value | divide:{2 + 3} }}', $data)));
        $this->assertSame(1, intval($this->renderString('{{ value | divide: {value} }}', $data)));
        $this->assertSame(1, intval($this->renderString('{{ value | divide: {2 + 3} }}', $data)));
        $this->assertSame(1, intval($this->renderString('{{ value | /: 5 }}', $data)));
        $this->assertSame(1, intval($this->renderString('{{ {value / 5} }}', $data)));
    }

    public function test_multiplication_modifier_shorthand_works()
    {
        $data = ['value' => 5];

        $this->assertSame(25, intval($this->renderString('{{ value * 5 }}', $data)));
        $this->assertSame(25, intval($this->renderString('{{ value | multiply: 5 }}', $data)));
        $this->assertSame(25, intval($this->renderString('{{ value | multiply:{value} }}', $data)));
        $this->assertSame(25, intval($this->renderString('{{ value | multiply:{2 + 3} }}', $data)));
        $this->assertSame(25, intval($this->renderString('{{ value | multiply: {value} }}', $data)));
        $this->assertSame(25, intval($this->renderString('{{ value | multiply: {2 + 3} }}', $data)));
        $this->assertSame(25, intval($this->renderString('{{ value | *: 5 }}', $data)));
        $this->assertSame(25, intval($this->renderString('{{ {value * 5} }}', $data)));
    }

    public function test_modulo_modifier_shorthand_works()
    {
        $data = ['value' => 5];

        $this->assertSame(0, intval($this->renderString('{{ value % 5 }}', $data)));
        $this->assertSame(0, intval($this->renderString('{{ value | mod: 5 }}', $data)));
        $this->assertSame(0, intval($this->renderString('{{ value | mod:{value} }}', $data)));
        $this->assertSame(0, intval($this->renderString('{{ value | mod:{2 + 3} }}', $data)));
        $this->assertSame(0, intval($this->renderString('{{ value | mod: {value} }}', $data)));
        $this->assertSame(0, intval($this->renderString('{{ value | mod: {2 + 3} }}', $data)));
        $this->assertSame(0, intval($this->renderString('{{ value | %: 5 }}', $data)));
        $this->assertSame(0, intval($this->renderString('{{ {value % 5} }}', $data)));
    }
}
