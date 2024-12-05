<?php

namespace Tests\Data;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Data\StoresComputedFieldCallbacks;
use Tests\TestCase;

class StoresComputedFieldCallbacksTest extends TestCase
{
    #[Test]
    public function it_can_store_computed_callback()
    {
        $repository = new FakeRepository;

        $repository->computed('some_field', $closureA = function ($item, $value) {
            //
        });

        $repository->computed('another_field', $closureB = function ($item, $value) {
            //
        });

        $this->assertEquals([
            'some_field' => $closureA,
            'another_field' => $closureB,
        ], $repository->getComputedCallbacks()->all());
    }
}

class FakeRepository
{
    use StoresComputedFieldCallbacks;
}
