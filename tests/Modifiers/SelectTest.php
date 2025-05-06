<?php

namespace Tests\Modifiers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Mockery;
use Statamic\Contracts\Query\Builder;
use Statamic\Entries\EntryCollection;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class SelectTest extends TestCase
{
    #[Test]
    public function it_selects_certain_values_from_array_of_items()
    {
        $items = $this->items();

        $modified = $this->modify($items, ['title', 'type']);
        $this->assertIsArray($modified);
        $this->assertEquals(
            [
                ['title' => 'Bread', 'type' => 'food'],
                ['title' => 'Coffee', 'type' => 'drink'],
            ],
            $modified,
        );

        $modified = $this->modify($items, ['title', 'stock']);
        $this->assertIsArray($modified);
        $this->assertEquals(
            [
                ['title' => 'Bread', 'stock' => 1],
                ['title' => 'Coffee', 'stock' => 2],
            ],
            $modified,
        );
    }

    #[Test]
    public function it_selects_certain_values_from_collections_of_items()
    {
        $items = Collection::make($this->items());

        $modified = $this->modify($items, ['title', 'type']);
        $this->assertInstanceOf(Collection::class, $modified);
        $this->assertEquals(
            [
                ['title' => 'Bread', 'type' => 'food'],
                ['title' => 'Coffee', 'type' => 'drink'],
            ],
            $modified->all(),
        );

        $modified = $this->modify($items, ['title', 'stock']);
        $this->assertInstanceOf(Collection::class, $modified);
        $this->assertEquals(
            [
                ['title' => 'Bread', 'stock' => 1],
                ['title' => 'Coffee', 'stock' => 2],
            ],
            $modified->all(),
        );
    }

    #[Test]
    public function it_selects_certain_values_from_query_builder()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('get')->andReturn(Collection::make($this->items()));

        $modified = $this->modify($builder, ['title', 'type']);
        $this->assertInstanceOf(Collection::class, $modified);
        $this->assertEquals(
            [
                ['title' => 'Bread', 'type' => 'food'],
                ['title' => 'Coffee', 'type' => 'drink'],
            ],
            $modified->all(),
        );

        $modified = $this->modify($builder, ['title', 'stock']);
        $this->assertInstanceOf(Collection::class, $modified);
        $this->assertEquals(
            [
                ['title' => 'Bread', 'stock' => 1],
                ['title' => 'Coffee', 'stock' => 2],
            ],
            $modified->all(),
        );
    }

    #[Test]
    public function it_selects_certain_values_from_array_of_items_with_origins()
    {
        $items = $this->itemsWithOrigins();

        $modified = $this->modify($items, ['title', 'type']);
        $this->assertIsArray($modified);
        $this->assertEquals(
            [
                ['title' => 'Bread', 'type' => 'food'],
                ['title' => 'Pan', 'type' => 'food'],
                ['title' => 'Coffee', 'type' => 'drink'],
                ['title' => 'Cafe', 'type' => 'drink'],
            ],
            $modified,
        );

        $modified = $this->modify($items, ['title', 'stock']);
        $this->assertIsArray($modified);
        $this->assertEquals(
            [
                ['title' => 'Bread', 'stock' => 1],
                ['title' => 'Pan', 'stock' => 1],
                ['title' => 'Coffee', 'stock' => 2],
                ['title' => 'Cafe', 'stock' => 2],
            ],
            $modified,
        );
    }

    #[Test]
    public function it_selects_certain_values_from_collections_of_items_with_origins()
    {
        $items = EntryCollection::make($this->itemsWithOrigins());

        $modified = $this->modify($items, ['title', 'type']);
        $this->assertInstanceOf(EntryCollection::class, $modified);
        $this->assertEquals(
            [
                ['title' => 'Bread', 'type' => 'food'],
                ['title' => 'Pan', 'type' => 'food'],
                ['title' => 'Coffee', 'type' => 'drink'],
                ['title' => 'Cafe', 'type' => 'drink'],
            ],
            $modified->all(),
        );

        $modified = $this->modify($items, ['title', 'stock']);
        $this->assertInstanceOf(EntryCollection::class, $modified);
        $this->assertEquals(
            [
                ['title' => 'Bread', 'stock' => 1],
                ['title' => 'Pan', 'stock' => 1],
                ['title' => 'Coffee', 'stock' => 2],
                ['title' => 'Cafe', 'stock' => 2],
            ],
            $modified->all(),
        );
    }

    #[Test]
    public function it_selects_certain_values_from_array_of_items_of_type_array()
    {
        $items = $this->itemsOfTypeArray();

        $modified = $this->modify($items, ['title', 'type']);
        $this->assertIsArray($modified);
        $this->assertEquals(
            [
                ['title' => 'Bread', 'type' => 'food'],
                ['title' => 'Coffee', 'type' => 'drink'],
            ],
            $modified,
        );

        $modified = $this->modify($items, ['title', 'stock']);
        $this->assertIsArray($modified);
        $this->assertEquals(
            [
                ['title' => 'Bread', 'stock' => 1],
                ['title' => 'Coffee', 'stock' => 2],
            ],
            $modified,
        );
    }

    #[Test]
    public function it_selects_certain_values_from_collections_of_items_of_type_array()
    {
        $items = EntryCollection::make($this->itemsOfTypeArray());

        $modified = $this->modify($items, ['title', 'type']);
        $this->assertInstanceOf(EntryCollection::class, $modified);
        $this->assertEquals(
            [
                ['title' => 'Bread', 'type' => 'food'],
                ['title' => 'Coffee', 'type' => 'drink'],
            ],
            $modified->all(),
        );

        $modified = $this->modify($items, ['title', 'stock']);
        $this->assertInstanceOf(EntryCollection::class, $modified);
        $this->assertEquals(
            [
                ['title' => 'Bread', 'stock' => 1],
                ['title' => 'Coffee', 'stock' => 2],
            ],
            $modified->all(),
        );
    }

    #[Test]
    public function it_selects_certain_values_from_array_of_items_of_type_arrayaccess()
    {
        $items = $this->itemsOfTypeArrayAccess();

        $modified = $this->modify($items, ['title', 'type']);
        $this->assertIsArray($modified);
        $this->assertEquals(
            [
                ['title' => 'Bread', 'type' => 'food'],
                ['title' => 'Coffee', 'type' => 'drink'],
            ],
            $modified,
        );

        $modified = $this->modify($items, ['title', 'stock']);
        $this->assertIsArray($modified);
        $this->assertEquals(
            [
                ['title' => 'Bread', 'stock' => 1],
                ['title' => 'Coffee', 'stock' => 2],
            ],
            $modified,
        );
    }

    private function items()
    {
        return [
            new Item(['title' => 'Bread', 'type' => 'food', 'stock' => 1]),
            new Item(['title' => 'Coffee', 'type' => 'drink', 'stock' => 2]),
        ];
    }

    private function itemsWithOrigins()
    {
        return [
            $breadEn = new ItemWithOrigin(['title' => 'Bread', 'type' => 'food', 'stock' => 1]),
            $breadEs = new ItemWithOrigin(['title' => 'Pan'], $breadEn),
            $coffeeEn = new ItemWithOrigin(['title' => 'Coffee', 'type' => 'drink', 'stock' => 2]),
            $coffeeEs = new ItemWithOrigin(['title' => 'Cafe'], $coffeeEn),
        ];
    }

    private function itemsOfTypeArray()
    {
        return [
            ['title' => 'Bread', 'type' => 'food', 'stock' => 1],
            ['title' => 'Coffee', 'type' => 'drink', 'stock' => 2],
        ];
    }

    private function itemsOfTypeArrayAccess()
    {
        return [
            new ArrayAccessType(['title' => 'Bread', 'type' => 'food', 'stock' => 1]),
            new ArrayAccessType(['title' => 'Coffee', 'type' => 'drink', 'stock' => 2]),
        ];
    }

    private function modify($value, ...$keys)
    {
        return Modify::value($value)->select(Arr::flatten($keys))->fetch();
    }
}
