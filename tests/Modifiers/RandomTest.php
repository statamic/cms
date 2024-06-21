<?php

namespace Tests\Modifiers;

use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Query\Builder;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class RandomTest extends TestCase
{
    #[Test]
    #[DataProvider('inputsProvider')]
    public function it_returns_one_random_item($input): void
    {
        $this->assertFalse(
            $this->modify($input) === $this->modify($input) && $this->modify($input) === $this->modify($input),
            'The same value was returned multiple times.',
        );
    }

    public static function inputsProvider()
    {
        $range = range(1, 5000);

        return [
            'array' => [$range],
            'collection' => [collect($range)],
            'query builder' => [Mockery::mock(Builder::class)->shouldReceive('get')->andReturn(collect($range))->getMock()],
        ];
    }

    private function modify($value)
    {
        return Modify::value($value)->random()->fetch();
    }
}
