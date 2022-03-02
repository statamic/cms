<?php

namespace Tests\Fieldtypes;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Query\Builder;
use Statamic\Data\AugmentedCollection;
use Statamic\Entries\EntryCollection;
use Statamic\Facades;
use Statamic\Facades\Site;
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

        Site::setConfig(['sites' => [
            'en' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]]);

        $collection = tap(Facades\Collection::make('blog')->routes('blog/{slug}'))->sites(['en', 'fr'])->dated(true)->pastDateBehavior('private')->futureDateBehavior('private')->save();

        EntryFactory::id('123')->collection($collection)->slug('one')->data(['title' => 'One'])->date('2021-01-02')->create();
        EntryFactory::id('456')->collection($collection)->slug('two')->data(['title' => 'Two'])->date('2021-01-02')->create();
        EntryFactory::id('789')->collection($collection)->slug('three')->data(['title' => 'Three'])->date('2021-01-02')->create();
        EntryFactory::id('910')->collection($collection)->slug('four')->data(['title' => 'Four'])->date('2021-01-02')->create();
        EntryFactory::id('draft')->collection($collection)->slug('draft')->data(['title' => 'Draft'])->published(false)->create();
        EntryFactory::id('scheduled')->collection($collection)->slug('scheduled')->data(['title' => 'Scheduled'])->date('2021-01-03')->create();
        EntryFactory::id('expired')->collection($collection)->slug('expired')->data(['title' => 'Expired'])->date('2021-01-01')->create();
    }

    /** @test */
    public function it_augments_to_a_query_builder()
    {
        $augmented = $this->fieldtype()->augment([456, 'invalid', '123', 'draft', 'scheduled', 'expired']);

        $this->assertInstanceOf(Builder::class, $augmented);
        $this->assertEveryItemIsInstanceOf(Entry::class, $augmented->get());
        $this->assertEquals(['456', '123'], $augmented->get()->map->id()->all());
    }

    /** @test */
    public function it_augments_to_a_query_builder_when_theres_no_value()
    {
        $augmented = $this->fieldtype()->augment(null);

        $this->assertInstanceOf(Builder::class, $augmented);
        $this->assertCount(0, $augmented->get());
    }

    /** @test */
    public function it_augments_to_a_single_asset_when_max_items_is_one()
    {
        $augmented = $this->fieldtype(['max_items' => 1])->augment(['123']);

        $this->assertInstanceOf(Entry::class, $augmented);
        $this->assertEquals('one', $augmented->slug());
    }

    /** @test */
    public function it_localizes_the_augmented_items_to_the_parent_entrys_locale()
    {
        $parent = EntryFactory::id('parent')->collection('blog')->slug('theparent')->locale('fr')->create();

        EntryFactory::id('123-fr')->origin('123')->locale('fr')->collection('blog')->slug('one-fr')->data(['title' => 'Le One'])->date('2021-01-02')->create();
        EntryFactory::id('789-fr')->origin('789')->locale('fr')->collection('blog')->slug('three-fr')->data(['title' => 'Le Three'])->date('2021-01-02')->published(false)->create();
        EntryFactory::id('910-fr')->origin('910')->locale('fr')->collection('blog')->slug('four-fr')->data(['title' => 'Le Four'])->date('2021-01-02')->create();

        $augmented = $this->fieldtype([], $parent)->augment(['123', 'invalid', 456, 789, 910, 'draft', 'scheduled', 'expired']);

        $this->assertInstanceOf(Builder::class, $augmented);
        $this->assertEveryItemIsInstanceOf(Entry::class, $augmented->get());
        $this->assertEquals(['one-fr', 'four-fr'], $augmented->get()->map->slug()->all()); // 456 isn't localized, and 789-fr is a draft.
    }

    /** @test */
    public function it_localizes_the_augmented_item_to_the_parent_entrys_locale_when_max_items_is_one()
    {
        $parent = EntryFactory::id('parent')->collection('blog')->slug('theparent')->locale('fr')->create();

        EntryFactory::id('123-fr')->origin('123')->locale('fr')->collection('blog')->slug('one-fr')->data(['title' => 'Le One'])->date('2021-01-02')->create();
        EntryFactory::id('789-fr')->origin('789')->locale('fr')->collection('blog')->slug('three-fr')->data(['title' => 'Le Three'])->date('2021-01-02')->published(false)->create();

        $fieldtype = $this->fieldtype(['max_items' => 1], $parent);

        $augmented = $fieldtype->augment(['123']);
        $this->assertInstanceOf(Entry::class, $augmented);
        $this->assertEquals('one-fr', $augmented->slug());

        $augmented = $fieldtype->augment(['456']);
        $this->assertNull($augmented); // 456 isnt localized

        $augmented = $fieldtype->augment(['789']);
        $this->assertNull($augmented); // 789-fr is a draft
    }

    /** @test */
    public function it_localizes_the_augmented_items_to_the_current_sites_locale_when_parent_is_not_localizable()
    {
        Site::setCurrent('fr');

        $parent = new class
        {
            // Class does not implement "Localizable"
        };

        EntryFactory::id('123-fr')->origin('123')->locale('fr')->collection('blog')->slug('one-fr')->data(['title' => 'Le One'])->date('2021-01-02')->create();
        EntryFactory::id('789-fr')->origin('789')->locale('fr')->collection('blog')->slug('three-fr')->data(['title' => 'Le Three'])->date('2021-01-02')->create();

        $augmented = $this->fieldtype([], $parent)->augment(['123', 'invalid', 456, 789, 'draft', 'scheduled', 'expired']);

        $this->assertInstanceOf(Builder::class, $augmented);
        $this->assertEveryItemIsInstanceOf(Entry::class, $augmented->get());
        $this->assertEquals(['one-fr', 'three-fr'], $augmented->get()->map->slug()->all()); // only 123 and 789 have localized versions
    }

    /** @test */
    public function it_localizes_the_augmented_item_to_the_current_sites_locale_when_parent_is_not_localizable_when_max_items_is_one()
    {
        Site::setCurrent('fr');

        $parent = new class
        {
            // Class does not implement "Localizable"
        };

        EntryFactory::id('123-fr')->origin('123')->locale('fr')->collection('blog')->slug('one-fr')->data(['title' => 'Le One'])->date('2021-01-02')->create();

        $fieldtype = $this->fieldtype(['max_items' => 1], $parent);

        $augmented = $fieldtype->augment(['123'], $parent);
        $this->assertInstanceOf(Entry::class, $augmented);
        $this->assertEquals('one-fr', $augmented->slug());

        $augmented = $fieldtype->augment(['456'], $parent);
        $this->assertNull($augmented); // 456 isnt localized
    }

    /** @test */
    public function it_shallow_augments_to_a_collection_of_entries()
    {
        $augmented = $this->fieldtype()->shallowAugment(['123', 'invalid', 456, 'draft', 'scheduled', 'expired']);

        $this->assertInstanceOf(Collection::class, $augmented);
        $this->assertNotInstanceOf(EntryCollection::class, $augmented);
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

    /** @test */
    public function it_localizes_the_shallow_augmented_items_to_the_parent_entrys_locale()
    {
        $parent = EntryFactory::id('parent')->collection('blog')->slug('theparent')->locale('fr')->create();

        EntryFactory::id('123-fr')->origin('123')->locale('fr')->collection('blog')->slug('one-fr')->data(['title' => 'Le One'])->date('2021-01-02')->create();
        EntryFactory::id('789-fr')->origin('789')->locale('fr')->collection('blog')->slug('three-fr')->data(['title' => 'Le Three'])->date('2021-01-02')->published(false)->create();
        EntryFactory::id('910-fr')->origin('910')->locale('fr')->collection('blog')->slug('four-fr')->data(['title' => 'Le Four'])->date('2021-01-02')->create();

        $augmented = $this->fieldtype([], $parent)->shallowAugment(['123', 'invalid', 456, 789, 910, 'draft', 'scheduled', 'expired']);

        $this->assertInstanceOf(Collection::class, $augmented);
        $this->assertEveryItemIsInstanceOf(AugmentedCollection::class, $augmented);
        $this->assertEquals([
            [
                'id' => '123-fr',
                'title' => 'Le One',
                'url' => '/fr/blog/one-fr',
                'permalink' => 'http://localhost/fr/blog/one-fr',
                'api_url' => 'http://localhost/api/collections/blog/entries/123-fr',
            ],
            [
                'id' => '910-fr',
                'title' => 'Le Four',
                'url' => '/fr/blog/four-fr',
                'permalink' => 'http://localhost/fr/blog/four-fr',
                'api_url' => 'http://localhost/api/collections/blog/entries/910-fr',
            ],
        ], $augmented->toArray()); // 456 isn't localized, and 789-fr is a draft.
    }

    /** @test */
    public function it_localizes_the_shallow_augmented_item_to_the_parent_entrys_locale_when_max_items_is_one()
    {
        $parent = EntryFactory::id('parent')->collection('blog')->slug('theparent')->locale('fr')->create();

        EntryFactory::id('123-fr')->origin('123')->locale('fr')->collection('blog')->slug('one-fr')->data(['title' => 'Le One'])->date('2021-01-02')->create();
        EntryFactory::id('789-fr')->origin('789')->locale('fr')->collection('blog')->slug('three-fr')->data(['title' => 'Le Three'])->date('2021-01-02')->published(false)->create();

        $fieldtype = $this->fieldtype(['max_items' => 1], $parent);

        $augmented = $fieldtype->shallowAugment(['123']);
        $this->assertInstanceOf(AugmentedCollection::class, $augmented);
        $this->assertEquals([
            'id' => '123-fr',
            'title' => 'Le One',
            'url' => '/fr/blog/one-fr',
            'permalink' => 'http://localhost/fr/blog/one-fr',
            'api_url' => 'http://localhost/api/collections/blog/entries/123-fr',
        ], $augmented->toArray());

        $augmented = $fieldtype->shallowAugment(['456']);
        $this->assertNull($augmented); // 456 isnt localized

        $augmented = $fieldtype->shallowAugment(['789']);
        $this->assertNull($augmented); // 789-fr is a draft
    }

    /** @test */
    public function it_localizes_the_shallow_augmented_items_to_the_current_sites_locale_when_parent_is_not_localizable()
    {
        Site::setCurrent('fr');

        $parent = new class
        {
            // Class does not implement "Localizable"
        };

        EntryFactory::id('123-fr')->origin('123')->locale('fr')->collection('blog')->slug('one-fr')->data(['title' => 'Le One'])->date('2021-01-02')->create();
        EntryFactory::id('789-fr')->origin('789')->locale('fr')->collection('blog')->slug('three-fr')->data(['title' => 'Le Three'])->date('2021-01-02')->create();

        $augmented = $this->fieldtype([], $parent)->shallowAugment(['123', 'invalid', 456, 789, 'draft', 'scheduled', 'expired']);

        $this->assertInstanceOf(Collection::class, $augmented);
        $this->assertEveryItemIsInstanceOf(AugmentedCollection::class, $augmented);
        $this->assertEquals([
            [
                'id' => '123-fr',
                'title' => 'Le One',
                'url' => '/fr/blog/one-fr',
                'permalink' => 'http://localhost/fr/blog/one-fr',
                'api_url' => 'http://localhost/api/collections/blog/entries/123-fr',
            ],
            [
                'id' => '789-fr',
                'title' => 'Le Three',
                'url' => '/fr/blog/three-fr',
                'permalink' => 'http://localhost/fr/blog/three-fr',
                'api_url' => 'http://localhost/api/collections/blog/entries/789-fr',
            ],
        ], $augmented->toArray()); // only 123 and 789 have localized versions
    }

    /** @test */
    public function it_localizes_the_shallow_augmented_item_to_the_current_sites_locale_when_parent_is_not_localizable_when_max_items_is_one()
    {
        Site::setCurrent('fr');

        $parent = new class
        {
            // Class does not implement "Localizable"
        };

        EntryFactory::id('123-fr')->origin('123')->locale('fr')->collection('blog')->slug('one-fr')->data(['title' => 'Le One'])->date('2021-01-02')->create();

        $fieldtype = $this->fieldtype(['max_items' => 1], $parent);

        $augmented = $fieldtype->shallowAugment(['123']);
        $this->assertInstanceOf(AugmentedCollection::class, $augmented);
        $this->assertEquals([
            'id' => '123-fr',
            'title' => 'Le One',
            'url' => '/fr/blog/one-fr',
            'permalink' => 'http://localhost/fr/blog/one-fr',
            'api_url' => 'http://localhost/api/collections/blog/entries/123-fr',
        ], $augmented->toArray());

        $augmented = $fieldtype->shallowAugment(['456']);
        $this->assertNull($augmented); // 456 isnt localized
    }

    public function fieldtype($config = [], $parent = null)
    {
        $field = new Field('test', array_merge([
            'type' => 'entries',
        ], $config));

        if ($parent) {
            $field->setParent($parent);
        }

        return (new Entries)->setField($field);
    }
}
