<?php

namespace Tests\Antlers\Fixtures\MethodClasses;

class ClassOne
{
    public function method($string)
    {
        return new ClassTwo($string);
    }
}
