<?php

namespace Tests\Antlers\Fixtures;

class TestClass
{
    public function noArgs()
    {
        return range(1, 5);
    }

    public function withArgs($start, $end)
    {
        return range($start, $end);
    }
}
