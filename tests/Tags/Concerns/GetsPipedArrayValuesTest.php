<?php

namespace Tests\Tags\Concerns;

use Statamic\Tags\Concerns\GetsPipedArrayValues;
use Tests\TestCase;

class GetsPipedArrayValuesTest extends TestCase
{
    use GetsPipedArrayValues;

    /** @test */
    public function it_filters_by_is_condition()
    {
        $this->assertEquals(['henry', true, false], $this->getPipedValues('henry|true|false'));
    }
}
