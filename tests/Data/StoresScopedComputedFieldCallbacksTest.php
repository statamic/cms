<?php

namespace Tests\Data;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Data\StoresScopedComputedFieldCallbacks;
use Tests\TestCase;

class StoresScopedComputedFieldCallbacksTest extends TestCase
{
    #[Test]
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

    #[Test]
    public function it_can_store_scoped_computed_callbacks_for_multiple_scopes()
    {
        $repository = new FakeRepositoryWithScopedCallbacks;

        $repository->computed(['events', 'articles'], 'some_field', $closure = function ($item, $value) {
            //
        });

        $this->assertEquals([
            'some_field' => $closure,
        ], $repository->getComputedCallbacks('events')->all());

        $this->assertEquals([
            'some_field' => $closure,
        ], $repository->getComputedCallbacks('articles')->all());
    }

    #[Test]
    public function it_can_store_multiple_scoped_computed_callbacks()
    {
        $repository = new FakeRepositoryWithScopedCallbacks;

        $repository->computed('events', [
            'some_field' => $closureA = function ($item, $value) {
                //
            },
        ]);

        $repository->computed('articles', [
            'some_field' => $closureB = function ($item, $value) {
                //
            },
            'another_field' => $closureC = function ($item, $value) {
                //
            },
        ]);

        $this->assertEquals([
            'some_field' => $closureA,
        ], $repository->getComputedCallbacks('events')->all());

        $this->assertEquals([
            'some_field' => $closureB,
            'another_field' => $closureC,
        ], $repository->getComputedCallbacks('articles')->all());

        $this->assertEquals([], $repository->getComputedCallbacks('products')->all());
    }

    #[Test]
    public function it_can_store_multiple_scoped_computed_callbacks_for_multiple_scopes()
    {
        $repository = new FakeRepositoryWithScopedCallbacks;

        $repository->computed(['events', 'articles'], [
            'some_field' => $closureA = function ($item, $value) {
                //
            },
            'another_field' => $closureB = function ($item, $value) {
                //
            },
        ]);

        $this->assertEquals([
            'some_field' => $closureA,
            'another_field' => $closureB,
        ], $repository->getComputedCallbacks('events')->all());

        $this->assertEquals([
            'some_field' => $closureA,
            'another_field' => $closureB,
        ], $repository->getComputedCallbacks('articles')->all());
    }
}

class FakeRepositoryWithScopedCallbacks
{
    use StoresScopedComputedFieldCallbacks;
}
