<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

/**
 * @group array
 */
class DoesntOverlapTest extends TestCase
{
    /** @test */
    public function it_returns_false_if_needle_found_in_array(): void
    {
        $haystack = ['a', 'b', 'c'];

        $modified = $this->modify($haystack, ['b'], []);
        $this->assertFalse($modified);
    }

    /** @test */
    public function it_returns_true_if_needle_is_not_found_in_array(): void
    {
        $haystack = ['a', 'b', 'c'];

        $modified = $this->modify($haystack, ['d'], []);
        $this->assertTrue($modified);
    }

    /** @test */
    public function it_returns_true_if_haystack_is_not_an_array(): void
    {
        $haystack = 'this is a string';

        $modified = $this->modify($haystack, ['d'], []);
        $this->assertTrue($modified);
    }

    /** @test */
    public function it_returns_false_if_needle_is_an_array_and_is_found_in_array(): void
    {
        $haystack = ['a', 'b', 'c'];

        $modified = $this->modify($haystack, ['array'], ['array' => ['a', 'b']]);
        $this->assertFalse($modified);
    }

    /** @test */
    public function it_returns_false_if_needle_is_an_array_and_some_are_not_found_in_array(): void
    {
        $haystack = ['a', 'b', 'c'];

        $modified = $this->modify($haystack, ['array'], ['array' => ['d', 'b']]);
        $this->assertFalse($modified);
    }

    private function modify($value, array $params, array $context)
    {
        return Modify::value($value)->context($context)->doesntOverlap($params)->fetch();
    }
}
