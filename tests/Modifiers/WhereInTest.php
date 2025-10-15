<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Statamic\Support\Arr;
use Tests\TestCase;

#[Group('array')]
class WhereInTest extends TestCase
{
    #[Test]
    public function it_filters_data_by_key_and_multiple_values(): void
    {
        $collection = [
            ['product' => 'Desk', 'price' => 200],
            ['product' => 'Chair', 'price' => 100],
            ['product' => 'Bookcase', 'price' => 150],
            ['product' => 'Door', 'price' => 100],
        ];
        $modified = $this->modify($collection, ['price', [150, 200]]);
        $this->assertEquals(['Desk', 'Bookcase'], Arr::pluck($modified, 'product'));
    }

    #[Test]
    public function it_filters_data_by_key_and_single_value(): void
    {
        $collection = [
            ['product' => 'Desk', 'price' => 200],
            ['product' => 'Chair', 'price' => 100],
            ['product' => 'Bookcase', 'price' => 150],
            ['product' => 'Door', 'price' => 100],
        ];
        $modified = $this->modify($collection, ['price', 100]);
        $this->assertEquals(['Chair', 'Door'], Arr::pluck($modified, 'product'));
    }

    private function modify($value, array $params)
    {
        return Modify::value($value)->whereIn($params)->fetch();
    }
}
