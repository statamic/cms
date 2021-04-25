<?php

namespace Tests\Modifiers;

use Statamic\Data\ContainsData;
use Statamic\Data\HasOrigin;
use Statamic\Entries\EntryCollection;
use Statamic\Modifiers\Modify;
use Statamic\Support\Traits\FluentlyGetsAndSets;
use Tests\TestCase;

class PluckTest extends TestCase
{
    /** @test */
    public function it_plucks_values_from_array_of_items()
    {
        $items = $this->items();

        $modified = $this->modify($items, 'title');
        $this->assertIsArray($modified);
        $this->assertEquals(['Bread', 'Coffee'], $modified);

        $modified = $this->modify($items, 'type');
        $this->assertIsArray($modified);
        $this->assertEquals(['food', 'drink'], $modified);
    }

    /** @test */
    public function it_plucks_values_from_collections_of_items()
    {
        $items = EntryCollection::make($this->items());

        $modified = $this->modify($items, 'title');
        $this->assertInstanceOf(EntryCollection::class, $modified);
        $this->assertEquals(['Bread', 'Coffee'], $modified->all());

        $modified = $this->modify($items, 'type');
        $this->assertInstanceOf(EntryCollection::class, $modified);
        $this->assertEquals(['food', 'drink'], $modified->all());
    }

    /** @test */
    public function it_plucks_values_from_array_of_items_with_origins()
    {
        $items = $this->itemsWithOrigins();

        $modified = $this->modify($items, 'title');
        $this->assertIsArray($modified);
        $this->assertEquals(['Bread', 'Pan', 'Coffee', 'Cafe'], $modified);

        $modified = $this->modify($items, 'type');
        $this->assertIsArray($modified);
        $this->assertEquals(['food', 'food', 'drink', 'drink'], $modified);
    }

    /** @test */
    public function it_plucks_values_from_collections_of_items_with_origins()
    {
        $items = EntryCollection::make($this->itemsWithOrigins());

        $modified = $this->modify($items, 'title');
        $this->assertInstanceOf(EntryCollection::class, $modified);
        $this->assertEquals(['Bread', 'Pan', 'Coffee', 'Cafe'], $modified->all());

        $modified = $this->modify($items, 'type');
        $this->assertInstanceOf(EntryCollection::class, $modified);
        $this->assertEquals(['food', 'food', 'drink', 'drink'], $modified->all());
    }

    private function items()
    {
        return [
            new Item(['title' => 'Bread', 'type' => 'food']),
            new Item(['title' => 'Coffee', 'type' => 'drink']),
        ];
    }

    private function itemsWithOrigins()
    {
        return [
            $breadEn = new ItemWithOrigin(['title' => 'Bread', 'type' => 'food']),
            $breadEs = new ItemWithOrigin(['title' => 'Pan'], $breadEn),
            $coffeeEn = new ItemWithOrigin(['title' => 'Coffee', 'type' => 'drink']),
            $coffeeEs = new ItemWithOrigin(['title' => 'Cafe'], $coffeeEn),
        ];
    }

    private function modify($value, $key)
    {
        return Modify::value($value)->pluck([$key])->fetch();
    }
}

// Represents an object that doesn't have origins and therefore wouldn't have a "value" method.
// So a "get" method would need to be used. e.g. a form Submission.
class Item
{
    use FluentlyGetsAndSets, ContainsData;

    public function __construct($data)
    {
        $this->data($data);
    }
}

// Represents an object that could have an origin and therefore a "value" method. e.g. an Entry.
class ItemWithOrigin
{
    use FluentlyGetsAndSets, ContainsData, HasOrigin;

    public function __construct($data, $origin = null)
    {
        $this->data($data);
        $this->origin($origin);
    }

    public function getOriginByString($origin)
    {
        //
    }
}
