<?php

namespace Tests\Modifiers;

use Mockery;
use Statamic\Contracts\Query\Builder;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class RandomTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider inputsProvider
     */
    public function it_returns_one_random_item($input): void
    {
        $this->assertFalse(
            $this->modify($input) === $this->modify($input) && $this->modify($input) === $this->modify($input),
            'The same value was returned multiple times.',
        );
    }

    public static function inputsProvider()
    {
        return [
            'array' => [range('a', 'z')],
            'collection' => [collect(range('a', 'z'))],
            'query builder' => [Mockery::mock(Builder::class)->shouldReceive('get')->andReturn(collect(range('a', 'z')))->getMock()],
        ];
    }

    private function modify($value)
    {
        return Modify::value($value)->random()->fetch();
    }
}
