<?php

namespace Tests\Antlers\Fixtures\MethodClasses;

class ArrayClass
{
    public function join($data)
    {
        return collect($data)->join(' ');
    }
}
