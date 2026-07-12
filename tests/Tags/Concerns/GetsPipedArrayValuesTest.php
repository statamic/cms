<?php

namespace Tests\Tags\Concerns;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Tags\Concerns\GetsPipedArrayValues;
use Tests\TestCase;

class GetsPipedArrayValuesTest extends TestCase
{
    use GetsPipedArrayValues;

    #[Test]
    public function it_filters_by_is_condition()
    {
        $this->assertEquals(['henry', true, false], $this->getPipedValues('henry|true|false'));
    }
}
