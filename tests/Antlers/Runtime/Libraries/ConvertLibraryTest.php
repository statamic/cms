<?php

namespace Tests\Antlers\Runtime\Libraries;

use Tests\Antlers\ParserTestCase;

class ConvertLibraryTest extends ParserTestCase
{
    public function test_convert_to_int()
    {
        $this->assertIsInt($this->evaluateRaw('convert.toInt("33")'));
        $this->assertIsInt($this->evaluateRaw('convert.toInt(false)'));
        $this->assertIsInt($this->evaluateRaw('convert.toInt(true)'));
    }

    public function test_convert_to_string()
    {
        $this->assertIsString($this->evaluateRaw('convert.toString(33)'));
        $this->assertIsString($this->evaluateRaw('convert.toString(false)'));
        $this->assertIsString($this->evaluateRaw('convert.toString(true)'));
    }

    public function test_convert_to_float()
    {
        $this->assertIsFloat($this->evaluateRaw('convert.toFloat(33)'));
        $this->assertIsFloat($this->evaluateRaw('convert.toFloat(33.2)'));
        $this->assertIsFloat($this->evaluateRaw('convert.toFloat("33.33")'));
    }

    public function test_convert_to_bool()
    {
        $this->assertIsBool($this->evaluateRaw('convert.toBool("1")'));
        $this->assertIsBool($this->evaluateRaw('convert.toBool("0")'));
        $this->assertIsBool($this->evaluateRaw('convert.toBool(false)'));
        $this->assertIsBool($this->evaluateRaw('convert.toBool(true)'));
        $this->assertIsBool($this->evaluateRaw('convert.toBool(1)'));
        $this->assertIsBool($this->evaluateRaw('convert.toBool(0)'));
    }
}
