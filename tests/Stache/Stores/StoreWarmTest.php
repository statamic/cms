<?php

namespace Tests\Stache\Stores;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Stache;
use Statamic\Stache\Indexes\Index;
use Statamic\Stache\Indexes\Value;
use Statamic\Stache\Stores\Store;
use Tests\TestCase;

class StoreWarmTest extends TestCase
{
    #[Test]
    public function it_builds_value_indexes_in_a_single_pass(): void
    {
        $store = (new WarmableTestStore)->withItems([
            'key-a' => 'Alpha',
            'key-b' => 'Beta',
        ]);

        Stache::registerStore($store);

        $store->warm();

        $this->assertEquals(
            ['key-a' => 'Alpha', 'key-b' => 'Beta'],
            $store->index('name')->items()->all()
        );
    }

    #[Test]
    public function it_handles_an_empty_store(): void
    {
        $store = (new WarmableTestStore)->withItems([]);

        Stache::registerStore($store);

        $store->warm();

        $this->assertEquals([], $store->index('name')->items()->all());
    }

    #[Test]
    public function it_updates_non_value_indexes_via_their_own_update_method(): void
    {
        $store = (new WarmableTestStore)->withItems([]);

        Stache::registerStore($store);

        $store->warm();

        $this->assertEquals(
            ['static-key' => 'static-value'],
            $store->index('static')->items()->all()
        );
    }
}

class WarmableTestStore extends Store
{
    protected array $items = [];

    protected $defaultIndexes = [];

    protected $storeIndexes = [
        'name' => WarmableNameIndex::class,
        'static' => WarmableStaticIndex::class,
    ];

    public function key(): string
    {
        return 'warmable';
    }

    public function withItems(array $items): static
    {
        $this->items = $items;

        return $this;
    }

    public function paths()
    {
        return collect(array_fill_keys(array_keys($this->items), '/fake'));
    }

    public function getItem($key): string
    {
        return $this->items[$key] ?? '';
    }

    public function getItemKey($item): string
    {
        return $item;
    }

    public function getItemValues($keys, $valueIndex, $keyIndex): array
    {
        return [];
    }
}

class WarmableNameIndex extends Value
{
    public function getItemValue($item): string
    {
        return $item;
    }
}

class WarmableStaticIndex extends Index
{
    public function getItems(): array
    {
        return ['static-key' => 'static-value'];
    }
}
