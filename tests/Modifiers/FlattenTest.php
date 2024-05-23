<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

/**
 * @todo Add test with numeric indices
 */
class FlattenTest extends TestCase
{
    /** @test */
    public function it_flattens_a_multidimensional_array(): void
    {
        $input = [
            'ingredients' => [
                'spices' => ['garlic', 'cumin', 'ginger', 'turmeric', 'paprika', 'curry powder'],
                'vegetables' => ['tomatoes', 'onion'],
                'meat' => ['chicken'],
            ],
        ];

        $expected = [
            'garlic',
            'cumin',
            'ginger',
            'turmeric',
            'paprika',
            'curry powder',
            'tomatoes',
            'onion',
            'chicken',
        ];

        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);

        $expected = [
            ['garlic', 'cumin', 'ginger', 'turmeric', 'paprika', 'curry powder'],
            ['tomatoes', 'onion'],
            ['chicken'],
        ];

        $modified = $this->modify($input, 1);
        $this->assertEquals($expected, $modified);
    }

    private function modify(array $value, $param = null)
    {
        return Modify::value($value)->flatten($param)->fetch();
    }
}
