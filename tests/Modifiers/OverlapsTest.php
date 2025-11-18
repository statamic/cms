<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

#[Group('array')]
class OverlapsTest extends TestCase
{
    #[Test]
    public function it_returns_true_if_needle_found_in_array(): void
    {
        $haystack = ['a', 'b', 'c'];

        $modified = $this->modify($haystack, ['b'], []);
        $this->assertTrue($modified);
    }

    #[Test]
    public function it_returns_false_if_needle_is_not_found_in_array(): void
    {
        $haystack = ['a', 'b', 'c'];

        $modified = $this->modify($haystack, ['d'], []);
        $this->assertFalse($modified);
    }

    #[Test]
    public function it_returns_false_if_haystack_is_not_an_array(): void
    {
        $haystack = 'this is a string';

        $modified = $this->modify($haystack, ['d'], []);
        $this->assertFalse($modified);
    }

    #[Test]
    public function it_returns_true_if_needle_is_an_array_and_is_found_in_array(): void
    {
        $haystack = ['a', 'b', 'c'];

        $modified = $this->modify($haystack, ['array'], ['array' => ['a', 'b']]);
        $this->assertTrue($modified);
    }

    #[Test]
    public function it_returns_true_if_needle_is_an_array_and_some_are_not_found_in_array(): void
    {
        $haystack = ['a', 'b', 'c'];

        $modified = $this->modify($haystack, ['array'], ['array' => ['d', 'b']]);
        $this->assertTrue($modified);
    }

    private function modify($value, array $params, array $context)
    {
        return Modify::value($value)->context($context)->overlaps($params)->fetch();
    }
}
