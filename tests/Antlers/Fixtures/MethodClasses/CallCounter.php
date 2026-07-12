<?php

namespace Tests\Antlers\Fixtures\MethodClasses;

class CallCounter
{
    protected $count = 0;

    public function increment()
    {
        $this->count += 1;

        return $this;
    }

    public function __toString(): string
    {
        return 'Count: '.$this->count;
    }
}
