<?php

namespace Tests\Stache;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blink;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class OrderableEntryUriTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $collection = Collection::make('vehicles')
            ->routes('/vehicles/{slug}')
            ->structureContents(['max_depth' => 1]);
        $collection->save();
    }

    private function simulateNewRequest(): void
    {
        Blink::flush();
        Stache::stores()->each->resetMemoizedState();
        app('stache.indexes')->each->resetMemoizedState();
    }

    #[Test]
    public function entries_have_uris_and_orders_after_being_saved()
    {
        Entry::make()->collection('vehicles')->slug('car')->data(['title' => 'Car'])->save();

        $this->simulateNewRequest();

        Entry::make()->collection('vehicles')->slug('bus')->data(['title' => 'Bus'])->save();

        $this->simulateNewRequest();

        $this->assertNotNull($car = Entry::findByUri('/vehicles/car'));
        $this->assertNotNull($bus = Entry::findByUri('/vehicles/bus'));
        $this->assertEquals(1, $car->order());
        $this->assertEquals(2, $bus->order());
    }
}
