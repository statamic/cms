<?php

namespace Tests\Antlers\Sandbox;

use Tests\Antlers\ParserTestCase;

class BitwiseOperatorsTest extends ParserTestCase
{
    public function test_bitwise_and()
    {
        $this->assertSame(strval(1 & 2), $this->renderString('{{ 1 bwa 2 }}'));
    }

    public function test_bitwise_or()
    {
        $this->assertSame(strval(1 | 2), $this->renderString('{{ 1 bwo 2 }}'));
    }

    public function test_bitwise_xor()
    {
        $this->assertSame(strval(1 ^ 2), $this->renderString('{{ 1 bxor 2 }}'));
    }

    public function test_bitwise_not()
    {
        $this->assertSame(strval(~2), $this->renderString('{{ bnot 2 }}'));
    }

    public function test_bitwise_shift_left()
    {
        $this->assertSame(strval(1 << 2), $this->renderString('{{ 1 bsl 2 }}'));
    }

    public function test_bitwise_shift_right()
    {
        $this->assertSame(strval(1 >> 2), $this->renderString('{{ 1 bsr 2 }}'));
    }
}
