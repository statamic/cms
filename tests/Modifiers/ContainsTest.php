<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

#[Group('array')]
class ContainsTest extends TestCase
{
    #[Test]
    public function it_returns_true_if_needle_found_in_string(): void
    {
        $haystack = 'It was the best of times, it was the worst of times.';

        $modified = $this->modify($haystack, ['BEST'], []);
        $this->assertTrue($modified);
    }

    #[Test]
    public function it_returns_false_if_needle_found_in_string_but_case_sensitivity_is_enabled(): void
    {
        $haystack = 'It was the best of times, it was the worst of times.';

        $modified = $this->modify($haystack, ['BEST', true], []);
        $this->assertFalse($modified);
    }

    #[Test]
    public function it_returns_true_in_string_if_the_field_name_exists_in_context(): void
    {
        $haystack = 'It was the best of times, it was the worst of times.';

        $modified = $this->modify($haystack, ['adjective'], ['adjective' => 'best', 'noun' => 'carrot']);
        $this->assertTrue($modified);
    }

    #[Test]
    public function it_returns_false_in_string_if_the_field_name_does_not_exists_in_context(): void
    {
        $haystack = 'It was the best of times, it was the worst of times.';

        $modified = $this->modify($haystack, ['noun'], ['adjective' => 'best', 'noun' => 'carrot']);
        $this->assertFalse($modified);
    }

    #[Test]
    public function it_returns_true_if_needle_found_in_array(): void
    {
        $haystack = ['bacon', 'bread', 'tomato'];

        $modified = $this->modify($haystack, ['bacon'], []);
        $this->assertTrue($modified);
    }

    #[Test]
    public function it_returns_true_if_needle_found_in_context_in_array(): void
    {
        $haystack = ['bacon', 'bread', 'tomato'];

        $modified = $this->modify($haystack, ['delicious'], ['delicious' => 'bacon', 'gross' => 'broccoli']);
        $this->assertTrue($modified);
    }

    #[Test]
    public function it_returns_false_if_needle_not_found_in_context_in_array(): void
    {
        $haystack = ['bacon', 'bread', 'tomato'];

        $modified = $this->modify($haystack, ['gross'], ['delicious' => 'bacon', 'gross' => 'broccoli']);
        $this->assertFalse($modified);
    }

    private function modify($value, array $params, array $context)
    {
        return Modify::value($value)->context($context)->contains($params)->fetch();
    }
}
