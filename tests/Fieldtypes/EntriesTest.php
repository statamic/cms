<?php

namespace Tests\Fieldtypes;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Statamic\Contracts\Entries\Entry;
use Statamic\Data\AugmentedCollection;
use Statamic\Facades;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Entries;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EntriesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2021-01-02'));

        $collection = tap(Facades\Collection::make('blog')->routes('blog/{slug}'))->dated(true)->pastDateBehavior('private')->futureDateBehavior('private')->save();

        EntryFactory::id('123')->collection($collection)->slug('one')->data(['title' => 'One'])->date('2021-01-02')->create();
        EntryFactory::id('456')->collection($collection)->slug('two')->data(['title' => 'Two'])->date('2021-01-02')->create();
        EntryFactory::id('draft')->collection($collection)->slug('draft')->data(['title' => 'Draft'])->published(false)->create();
        EntryFactory::id('scheduled')->collection($collection)->slug('scheduled')->data(['title' => 'Scheduled'])->date('2021-01-03')->create();
        EntryFactory::id('expired')->collection($collection)->slug('expired')->data(['title' => 'Expired'])->date('2021-01-01')->create();
    }

    /** @test */
    public function it_augments_to_a_collection_of_entries()
    {
        $augmented = $this->fieldtype()->augment(['123', 'invalid', 456, 'draft', 'scheduled', 'expired']);

        $this->assertInstanceOf(Collection::class, $augmented);
        $this->assertEveryItemIsInstanceOf(Entry::class, $augmented);
        $this->assertEquals(['one', 'two'], $augmented->map->slug()->all());
    }

    /** @test */
    public function it_augments_to_a_single_asset_when_max_items_is_one()
    {
        $augmented = $this->fieldtype(['max_items' => 1])->augment(['123']);

        $this->assertInstanceOf(Entry::class, $augmented);
        $this->assertEquals('one', $augmented->slug());
    }

    /** @test */
    public function it_shallow_augments_to_a_collection_of_enties()
    {
        $augmented = $this->fieldtype()->shallowAugment(['123', '456']);

        $this->assertInstanceOf(Collection::class, $augmented);
        $this->assertEveryItemIsInstanceOf(AugmentedCollection::class, $augmented);
        $this->assertEquals([
            [
                'id' => '123',
                'title' => 'One',
                'url' => '/blog/one',
                'permalink' => 'http://localhost/blog/one',
                'api_url' => 'http://localhost/api/collections/blog/entries/123',
            ],
            [
                'id' => '456',
                'title' => 'Two',
                'url' => '/blog/two',
                'permalink' => 'http://localhost/blog/two',
                'api_url' => 'http://localhost/api/collections/blog/entries/456',
            ],
        ], $augmented->toArray());
    }

    /** @test */
    public function it_shallow_augments_to_a_single_entry_when_max_items_is_one()
    {
        $augmented = $this->fieldtype(['max_items' => 1])->shallowAugment(['123']);

        $this->assertInstanceOf(AugmentedCollection::class, $augmented);
        $this->assertEquals([
            'id' => '123',
            'title' => 'One',
            'url' => '/blog/one',
            'permalink' => 'http://localhost/blog/one',
            'api_url' => 'http://localhost/api/collections/blog/entries/123',
        ], $augmented->toArray());
    }

    public function fieldtype($config = [])
    {
        return (new Entries)->setField(new Field('test', array_merge([
            'type' => 'entries',
        ], $config)));
    }
}
