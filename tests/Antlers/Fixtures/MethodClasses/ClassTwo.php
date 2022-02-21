<?php

namespace Tests\Antlers\Fixtures\MethodClasses;

class ClassTwo
{
    protected $value = '';

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function methodTwo()
    {
        return 'Value: '.$this->value;
    }

    public function __toString()
    {
        return 'String: '.$this->value;
    }
}
