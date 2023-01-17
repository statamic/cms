<?php

namespace Tests\Data;

use Statamic\Data\StoresScopedComputedFieldCallbacks;
use Tests\TestCase;

class StoresScopedComputedFieldCallbacksTest extends TestCase
{
    /** @test */
    public function it_can_store_scoped_computed_callbacks()
    {
        $repository = new FakeRepositoryWithScopedCallbacks;

        $repository->computed('events', 'some_field', $closureA = function ($item, $value) {
            //
        });

        $repository->computed('articles', 'some_field', $closureB = function ($item, $value) {
            //
        });

        $repository->computed('articles', 'another_field', $closureC = function ($item, $value) {
            //
        });

        $this->assertEquals([
            'some_field' => $closureA,
        ], $repository->getComputedCallbacks('events')->all());

        $this->assertEquals([
            'some_field' => $closureB,
            'another_field' => $closureC,
        ], $repository->getComputedCallbacks('articles')->all());

        $this->assertEquals([], $repository->getComputedCallbacks('products')->all());
    }
}

class FakeRepositoryWithScopedCallbacks
{
    use StoresScopedComputedFieldCallbacks;
}
