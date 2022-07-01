<?php

namespace Tests\Data;

use Exception;
use Statamic\Data\StoresComputedFieldCallbacks;
use Tests\TestCase;
use TypeError;

class StoresComputedFieldCallbacksTest extends TestCase
{
    /** @test */
    public function it_can_store_computed_callback()
    {
        $repository = new FakeRepository;

        $repository->computed('some_field', function ($item, $attribute) {
            //
        });

        $repository->computed('another_field', function ($item, $attribute) {
            //
        });

        $this->assertCount(2, $repository->getComputedCallbacks());
    }

    /** @test */
    public function storing_callback_requires_two_arguments_by_default()
    {
        $repository = new FakeRepository;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Number of arguments required: 2');

        $repository->computed('articles', 'some_field', function ($item, $attribute) {
            //
        });
    }

    /** @test */
    public function storing_callback_requires_last_argument_to_be_closure()
    {
        $repository = new FakeRepository;

        $this->expectException(TypeError::class);

        $repository->computed('some_field', 'not a closure');
    }

    /** @test */
    public function it_can_store_a_scoped_computed_callback()
    {
        $repository = new FakeRepositoryWithScopedCallbacks;

        $repository->computed('articles', 'some_field', function ($item, $attribute) {
            //
        });

        $repository->computed('articles', 'another_field', function ($item, $attribute) {
            //
        });

        $this->assertCount(2, $repository->getComputedCallbacks());
    }

    /** @test */
    public function storing_scoped_callback_requires_three_arguments()
    {
        $repository = new FakeRepositoryWithScopedCallbacks;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Number of arguments required: 3');

        $repository->computed('some_field', function ($item, $attribute) {
            //
        });
    }

    /** @test */
    public function storing_scoped_callback_requires_last_argument_to_be_closure()
    {
        $repository = new FakeRepositoryWithScopedCallbacks;

        $this->expectException(TypeError::class);

        $repository->computed('articles', 'some_field', 'not a closure');
    }
}

class FakeRepository
{
    use StoresComputedFieldCallbacks;
}

class FakeRepositoryWithScopedCallbacks
{
    use StoresComputedFieldCallbacks;

    protected $scopeComputedFieldCallbacks = true;
}
