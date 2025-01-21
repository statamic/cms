<?php

namespace Tests\Modifiers;

use Illuminate\Support\Collection;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Query\Builder;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class ShuffleTest extends TestCase
{
    #[Test]
    #[DataProvider('inputsProvider')]
    public function it_shuffles_the_items($input, $expectedType): void
    {
        $this->assertFalse(
            $this->modify($input) === $this->modify($input) && $this->modify($input) === $this->modify($input),
            'The same value was returned multiple times.',
        );

        $this->assertTrue(
            $expectedType($this->modify($input)),
            'The modified value is not of the expected type.'
        );
    }

    public static function inputsProvider()
    {
        return [
            'array' => [
                range('a', 'z'),
                fn ($value) => is_array($value),
            ],
            'collection' => [
                collect(range('a', 'z')),
                fn ($value) => $value instanceof Collection,
            ],
            'query builder' => [
                Mockery::mock(Builder::class)->shouldReceive('get')->andReturn(collect(range('a', 'z')))->getMock(),
                fn ($value) => $value instanceof Collection,
            ],
        ];
    }

    private function modify($value)
    {
        return Modify::value($value)->shuffle()->fetch();
    }
}
