<?php

namespace Tests\Fieldtypes\Concerns;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

trait TestsQueryableValueWithMaxItems
{
    public static function maxItemsProvider()
    {
        // * Maybe the config changed since the value was saved

        return [
            'max undefined, single item array' => [null, ['a'], ['a']],
            'max undefined, multiple item array' => [null, ['a', 'b'], ['a', 'b']],
            'max undefined, empty array' => [null, [], null],
            'max undefined, null' => [null, null, null],
            'max undefined, string' => [null, 'a', ['a']], // *

            'max > 1, single item array' => [2, ['a'], ['a']],
            'max > 1, multiple item array' => [2, ['a', 'b'], ['a', 'b']],
            'max > 1, empty array' => [2, [], null],
            'max > 1, null' => [2, null, null],
            'max > 1, string' => [2, 'a', ['a']], // *

            'max = 1, single item array' => [1, ['a'], 'a'], // *
            'max = 1, multiple item array' => [1, ['a', 'b'], 'a'], // *
            'max = 1, empty array' => [1, [], null], // *
            'max = 1, null' => [1, null, null],
            'max = 1, string' => [1, 'a', 'a'],
        ];
    }

    #[Test]
    #[DataProvider('maxItemsProvider')]
    public function it_normalizes_queryable_value($maxItems, $value, $expectedValue)
    {
        $config = [];

        if ($maxItems) {
            $config[$this->maxItemsConfigKey()] = $maxItems;
        }

        $field = $this->fieldtype($config);

        $this->assertEquals($expectedValue, $field->toQueryableValue($value));
    }

    private function maxItemsConfigKey()
    {
        return 'max_items';
    }
}
