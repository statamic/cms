<?php

namespace Tests\Rules;

use Illuminate\Support\Facades\Validator;

trait ValidatesCustomRule
{
    public function validator($string)
    {
        return Validator::make(
            ['input' => $string],
            ['input' => [new static::$customRule]]
        );
    }

    public function assertPasses($string)
    {
        return $this->assertTrue($this->validator($string)->passes());
    }

    public function assertFails($string)
    {
        return $this->assertFalse($this->validator($string)->passes());
    }

    public function assertValidationErrorOutput($expectedErrorMessage, $badInput)
    {
        $this->assertEquals($expectedErrorMessage, $this->validator($badInput)->errors()->first());
    }
}
