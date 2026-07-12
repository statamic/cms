<?php

namespace Tests\Antlers\Fixtures\MethodClasses;

class StringLengthObject
{
    protected $value = '';

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function length()
    {
        return strlen($this->value);
    }
}
