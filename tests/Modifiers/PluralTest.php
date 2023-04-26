<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

/**
 * @group array
 */
class PluralTest extends TestCase
{
    public function shoppingList()
    {
        return [
            ['pickle', ['item' => 'pickle', 'quantity' => 1]],
            ['apples', ['item' => 'apple', 'quantity' => 12]],
            ['donuts', ['item' => 'donut', 'quantity' => 500]],
        ];
    }

    /**
     * @test
     *
     * @dataProvider shoppingList
     */
    public function it_returns_the_plural_form_of_an_english_word_from_context($expected, $input): void
    {
        $modified = $this->modify(
            $input['item'],
            ['quantity'],
            [
                'item' => $input['item'],
                'quantity' => $input['quantity'],
            ]
        );
        $this->assertEquals($expected, $modified);
    }

    /**
     * @test
     */
    public function it_returns_the_plural_form_of_an_english_word_from_parameter(): void
    {
        $modified = $this->modify('peanut', [10], []);
        $this->assertEquals('peanuts', $modified);
    }

    private function modify($value, array $params, array $context)
    {
        return Modify::value($value)->context($context)->plural($params)->fetch();
    }
}
