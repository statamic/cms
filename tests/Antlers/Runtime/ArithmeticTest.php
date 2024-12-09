<?php

namespace Tests\Antlers\Runtime;

use Tests\Antlers\ParserTestCase;

class ArithmeticTest extends ParserTestCase
{
    public function test_multiplication_before_addition()
    {
        $this->assertSame(10, intval($this->renderString('{{ 2 * 10 - 10 }}', [])));
        $this->assertSame(10, intval($this->renderString('{{ 30 - 2 * 10 }}', [])));
    }

    public function test_zeroes_can_be_incremented()
    {
        $this->assertSame(15, intval($this->renderString('{{ test_count = 0;
                test_count += 5;
                test_count += 10;
                test_count; }}')));
    }

    public function test_subtraction_operator()
    {
        $this->assertSame('2', $this->renderString('{{ 1--1 }}'));
    }

    public function test_subtracting_negative_numbers()
    {
        $this->assertSame(0, (int) $this->evaluateRaw('-1 - -1'));
        $this->assertSame(0, (int) $this->evaluateRaw('-1--1'));
        $this->assertSame(-2, (int) $this->evaluateRaw('-1-1'));
        $this->assertSame(-2, (int) $this->evaluateRaw('(-1)-1'));
        $this->assertSame(-2, (int) $this->renderString('{{ {-1}-1 }}'));
    }

    public function test_arithmetic_bordering_interpolation_regions()
    {
        $this->assertSame(-1, (int) $this->renderString('{{ {-1}/1 }}'));
        $this->assertSame(-1, (int) $this->renderString('{{ {-1}**1 }}'));
        $this->assertSame(-1, (int) $this->renderString('{{ {-1}*1 }}'));
        $this->assertSame(0, (int) $this->renderString('{{ {-1}+1 }}'));
        $this->assertSame(0, (int) $this->renderString('{{ {-1}%1 }}'));
    }

    public function test_add_three_variables_and_get_result()
    {
        $data = $this->evaluate('a = 1; b = 2; c = 3; d = a + b + c;', []);

        $this->assertSame(6, intval($data['d']));
    }

    public function test_divide_three_variables_get_result()
    {
        $data = $this->evaluate('a = 27; b= 3; c = 3; d = a/b/c;', []);
        $this->assertSame(3, intval($data['d']));
    }

    public function test_subtract_three_variables_and_get_result()
    {
        $data = $this->evaluate('a = 27; b = 7; c = 10; d = a - b - c;', []);
        $this->assertSame(10, intval($data['d']));
    }

    public function test_factorial_operator()
    {
        $this->assertSame(120, intval($this->renderString('{{ 5! }}')));
        $this->assertSame(120, intval($this->renderString('{{ (2 + 3)! }}')));
        $this->assertSame(122, intval($this->renderString('{{ (2 + 3)! + 2}}')));
        $this->assertSame(240, intval($this->renderString('{{ (2 + 3)! + 5!}}')));

        // Note: This is not the same as "double factorial". This syntax is just iterative:
        $this->assertSame(720, intval($this->renderString('{{ 3!! }}')));
    }

    public function test_modulus_operator()
    {
        $this->assertSame('Yes', $this->renderString('{{ if 6 % 2 == 0 }}Yes{{ else }}No{{ endif }}'));
        $this->assertSame('No', $this->renderString('{{ if 6 % 2 == 1 }}Yes{{ else }}No{{ endif }}'));
    }

    public function test_subtraction_after_logic_groups()
    {
        $data = [
            'items' => ['a', 'b', 'c'],
        ];

        $this->assertSame(2, intval($this->renderString('{{ (items|length) - 1 }}', $data, true)));
    }
}
