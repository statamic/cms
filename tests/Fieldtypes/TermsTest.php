<?php

namespace Tests\Fieldtypes;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Collection;
use Statamic\Contracts\Entries\Collection as CollectionContract;
use Statamic\Contracts\Query\Builder;
use Statamic\Data\AugmentedCollection;
use Statamic\Facades;
use Statamic\Facades\Site;
use Statamic\Facades\Term;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Terms;
use Statamic\Taxonomies\LocalizedTerm;
use Statamic\Taxonomies\TermCollection;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TermsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Site::setConfig(['sites' => [
            'en' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]]);

        Facades\Collection::make('blog')->taxonomies(['tags'])->save();
        Facades\Taxonomy::make('tags')->sites(['en', 'fr'])->save();
        Facades\Taxonomy::make('categories')->sites(['en', 'fr'])->save();

        Term::make('one')->taxonomy('tags')
            ->dataForLocale('en', ['title' => 'One'])
            ->dataForLocale('fr', ['title' => 'Un'])
            ->save();
        Term::make('two')->taxonomy('tags')
            ->dataForLocale('en', ['title' => 'Two'])
            ->dataForLocale('fr', ['title' => 'Deux'])
            ->save();
        Term::make('three')->taxonomy('tags')
            ->dataForLocale('en', ['title' => 'Three'])
            // intentionally no french version
            ->save();
        Term::make('red')->taxonomy('categories')
            ->dataForLocale('en', ['title' => 'Red'])
            ->dataForLocale('fr', ['title' => 'Rouge'])
            ->save();
    }

    /** @test */
    public function it_augments_slugs_to_a_query_builder_when_using_a_single_taxonomy()
    {
        $augmented = $this->fieldtype(['taxonomies' => 'tags'])->augment(['one', 'two']);

        $this->assertInstanceOf(Builder::class, $augmented);
        $this->assertEveryItemIsInstanceOf(LocalizedTerm::class, $augmented->get());
        $this->assertEquals(['tags::one', 'tags::two'], $augmented->get()->map->id()->all());
    }

    /** @test */
    public function it_augments_ids_to_a_query_builder_when_using_multiple_taxonomies()
    {
        $augmented = $this->fieldtype(['taxonomies' => ['tags', 'categories']])->augment(['tags::one', 'categories::red']);

        $this->assertInstanceOf(Builder::class, $augmented);
        $this->assertEveryItemIsInstanceOf(LocalizedTerm::class, $augmented->get());
        $this->assertEquals(['tags::one', 'categories::red'], $augmented->get()->map->id()->all());
    }

    /** @test */
    public function it_throws_an_exception_when_augmenting_a_slug_when_using_multiple_taxonomies()
    {
        $this->expectExceptionMessage('Ambigious taxonomy term value [one]. Field [test] is configured with multiple taxonomies.');

        $this->fieldtype(['taxonomies' => ['tags', 'categories']])->augment(['one', 'two']);
    }

    /** @test */
    public function it_augments_to_a_single_term_when_max_items_is_one()
    {
        $augmented = $this->fieldtype(['taxonomies' => 'tags', 'max_items' => 1])->augment(['one']);

        $this->assertInstanceOf(LocalizedTerm::class, $augmented);
        $this->assertEquals('tags::one', $augmented->id());
    }

    /** @test */
    public function it_localizes_the_augmented_items_to_the_parent_entrys_locale()
    {
        $parent = EntryFactory::id('parent')->collection('blog')->slug('theparent')->locale('fr')->create();

        $augmented = $this->fieldtype(['taxonomies' => 'tags'], $parent)->augment(['one', 'two', 'three']);

        $this->assertInstanceOf(Builder::class, $augmented);
        $this->assertEveryItemIsInstanceOf(LocalizedTerm::class, $augmented->get());
        $this->assertEquals(['fr', 'fr', 'fr'], $augmented->get()->map->locale->all());
        $this->assertEquals(['Un', 'Deux', 'Three'], $augmented->get()->map->title->all());
    }

    /** @test */
    public function it_localizes_the_augmented_item_to_the_parent_entrys_locale_when_max_items_is_one()
    {
        $parent = EntryFactory::id('parent')->collection('blog')->slug('theparent')->locale('fr')->create();

        $fieldtype = $this->fieldtype(['taxonomies' => 'tags', 'max_items' => 1], $parent);

        $augmented = $fieldtype->augment(['one']);
        $this->assertInstanceOf(LocalizedTerm::class, $augmented);
        $this->assertEquals('fr', $augmented->locale());
        $this->assertEquals('Un', $augmented->title());

        $augmented = $fieldtype->augment(['three']);
        $this->assertInstanceOf(LocalizedTerm::class, $augmented);
        $this->assertEquals('fr', $augmented->locale());
        $this->assertEquals('Three', $augmented->title());
    }

    /** @test */
    public function it_localizes_the_augmented_items_to_the_current_sites_locale_when_parent_is_not_localizable()
    {
        Site::setCurrent('fr');

        $parent = new class
        {
            // Class does not implement "Localizable"
        };

        $augmented = $this->fieldtype(['taxonomies' => 'tags'], $parent)->augment(['one', 'two', 'three']);

        $this->assertInstanceOf(Builder::class, $augmented);
        $this->assertEveryItemIsInstanceOf(LocalizedTerm::class, $augmented->get());
        $this->assertEquals(['fr', 'fr', 'fr'], $augmented->get()->map->locale->all());
        $this->assertEquals(['Un', 'Deux', 'Three'], $augmented->get()->map->title->all());
    }

    /** @test */
    public function it_localizes_the_augmented_item_to_the_current_sites_locale_when_parent_is_not_localizable_when_max_items_is_one()
    {
        Site::setCurrent('fr');

        $parent = new class
        {
            // Class does not implement "Localizable"
        };

        $fieldtype = $this->fieldtype(['taxonomies' => 'tags', 'max_items' => 1], $parent);

        $augmented = $fieldtype->augment(['one']);
        $this->assertInstanceOf(LocalizedTerm::class, $augmented);
        $this->assertEquals('fr', $augmented->locale());
        $this->assertEquals('Un', $augmented->title());

        $augmented = $fieldtype->augment(['three']);
        $this->assertInstanceOf(LocalizedTerm::class, $augmented);
        $this->assertEquals('fr', $augmented->locale());
        $this->assertEquals('Three', $augmented->title());
    }

    /** @test */
    public function it_shallow_augments_slugs_to_a_collection_of_terms_when_using_a_single_taxonomy()
    {
        $augmented = $this->fieldtype(['taxonomies' => 'tags'])->shallowAugment(['one', 'two']);

        $this->assertInstanceOf(Collection::class, $augmented);
        $this->assertNotInstanceOf(TermCollection::class, $augmented);
        $this->assertEveryItemIsInstanceOf(AugmentedCollection::class, $augmented);
        $this->assertCount(2, $augmented);
        $this->assertEquals([
            [
                'id' => 'tags::one',
                'slug' => 'one',
                'title' => 'One',
                'url' => '/tags/one',
                'permalink' => 'http://localhost/tags/one',
                'api_url' => 'http://localhost/api/taxonomies/tags/terms/one',
            ],
            [
                'id' => 'tags::two',
                'slug' => 'two',
                'title' => 'Two',
                'url' => '/tags/two',
                'permalink' => 'http://localhost/tags/two',
                'api_url' => 'http://localhost/api/taxonomies/tags/terms/two',
            ],
        ], $augmented->toArray());
    }

    /** @test */
    public function it_shallow_augments_to_a_single_term_when_max_items_is_one()
    {
        $augmented = $this->fieldtype(['taxonomies' => 'tags', 'max_items' => 1])->shallowAugment(['one']);

        $this->assertInstanceOf(AugmentedCollection::class, $augmented);
        $this->assertEquals([
            'id' => 'tags::one',
            'title' => 'One',
            'slug' => 'one',
            'url' => '/tags/one',
            'permalink' => 'http://localhost/tags/one',
            'api_url' => 'http://localhost/api/taxonomies/tags/terms/one',
        ], $augmented->toArray());
    }

    /** @test */
    public function it_localizes_the_shallow_augmented_items_to_the_parent_entrys_locale()
    {
        $parent = EntryFactory::id('parent')->collection('blog')->slug('theparent')->locale('fr')->create();

        $augmented = $this->fieldtype(['taxonomies' => 'tags'], $parent)->shallowAugment(['one', 'three']);

        $this->assertInstanceOf(Collection::class, $augmented);
        $this->assertEveryItemIsInstanceOf(AugmentedCollection::class, $augmented);
        $this->assertEquals([
            [
                'id' => 'tags::one',
                'title' => 'Un',
                'slug' => 'one',
                'url' => '/fr/tags/one',
                'permalink' => 'http://localhost/fr/tags/one',
                'api_url' => 'http://localhost/api/taxonomies/tags/terms/one', // doesn't seem accurate. is there a site specific url?
            ],
            [
                'id' => 'tags::three',
                'title' => 'Three',
                'slug' => 'three',
                'url' => '/fr/tags/three',
                'permalink' => 'http://localhost/fr/tags/three',
                'api_url' => 'http://localhost/api/taxonomies/tags/terms/three', // doesn't seem accurate. is there a site specific url?
            ],
        ], $augmented->toArray());
    }

    /** @test */
    public function it_localizes_the_shallow_augmented_item_to_the_parent_entrys_locale_when_max_items_is_one()
    {
        $parent = EntryFactory::id('parent')->collection('blog')->slug('theparent')->locale('fr')->create();

        $fieldtype = $this->fieldtype(['taxonomies' => 'tags', 'max_items' => 1], $parent);

        $augmented = $fieldtype->shallowAugment(['one']);
        $this->assertInstanceOf(AugmentedCollection::class, $augmented);
        $this->assertEquals([
            'id' => 'tags::one',
            'title' => 'Un',
            'slug' => 'one',
            'url' => '/fr/tags/one',
            'permalink' => 'http://localhost/fr/tags/one',
            'api_url' => 'http://localhost/api/taxonomies/tags/terms/one', // doesn't seem accurate. is there a site specific url?
        ], $augmented->toArray());

        $augmented = $fieldtype->shallowAugment(['three']);
        $this->assertInstanceOf(AugmentedCollection::class, $augmented);
        $this->assertEquals([
            'id' => 'tags::three',
            'title' => 'Three',
            'slug' => 'three',
            'url' => '/fr/tags/three',
            'permalink' => 'http://localhost/fr/tags/three',
            'api_url' => 'http://localhost/api/taxonomies/tags/terms/three', // doesn't seem accurate. is there a site specific url?
        ], $augmented->toArray());
    }

    /** @test */
    public function it_localizes_the_shallow_augmented_items_to_the_current_sites_locale_when_parent_is_not_localizable()
    {
        Site::setCurrent('fr');

        $parent = new class
        {
            // Class does not implement "Localizable"
        };

        $augmented = $this->fieldtype(['taxonomies' => 'tags'], $parent)->shallowAugment(['one', 'three']);

        $this->assertInstanceOf(Collection::class, $augmented);
        $this->assertEveryItemIsInstanceOf(AugmentedCollection::class, $augmented);
        $this->assertEquals([
            [
                'id' => 'tags::one',
                'title' => 'Un',
                'slug' => 'one',
                'url' => '/fr/tags/one',
                'permalink' => 'http://localhost/fr/tags/one',
                'api_url' => 'http://localhost/api/taxonomies/tags/terms/one', // doesn't seem accurate. is there a site specific url?
            ],
            [
                'id' => 'tags::three',
                'title' => 'Three',
                'slug' => 'three',
                'url' => '/fr/tags/three',
                'permalink' => 'http://localhost/fr/tags/three',
                'api_url' => 'http://localhost/api/taxonomies/tags/terms/three', // doesn't seem accurate. is there a site specific url?
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

        $fieldtype = $this->fieldtype(['taxonomies' => 'tags', 'max_items' => 1], $parent);

        $augmented = $fieldtype->shallowAugment(['one']);
        $this->assertInstanceOf(AugmentedCollection::class, $augmented);
        $this->assertEquals([
            'id' => 'tags::one',
            'title' => 'Un',
            'slug' => 'one',
            'url' => '/fr/tags/one',
            'permalink' => 'http://localhost/fr/tags/one',
            'api_url' => 'http://localhost/api/taxonomies/tags/terms/one', // doesn't seem accurate. is there a site specific url?
        ], $augmented->toArray());

        $augmented = $fieldtype->shallowAugment(['three']);
        $this->assertInstanceOf(AugmentedCollection::class, $augmented);
        $this->assertEquals([
            'id' => 'tags::three',
            'title' => 'Three',
            'slug' => 'three',
            'url' => '/fr/tags/three',
            'permalink' => 'http://localhost/fr/tags/three',
            'api_url' => 'http://localhost/api/taxonomies/tags/terms/three', // doesn't seem accurate. is there a site specific url?
        ], $augmented->toArray());
    }

    /**
     * @test
     * @dataProvider collectionAttachmentProvider
     **/
    public function it_attaches_collection_during_augmentation($parentIsEntry, $handle)
    {
        // Make sure there is an entry that uses the term.
        EntryFactory::collection('blog')->data(['tags' => ['one']])->create();

        if ($parentIsEntry) {
            $parent = EntryFactory::id('parent')->collection('blog')->slug('theparent')->locale('fr')->create();
        } else {
            $parent = new class
            {
                // Class does not implement "Localizable"
            };
        }

        $fieldtype = $this->fieldtype(['taxonomies' => 'tags'], $parent, $handle);

        $augmented = $fieldtype->augment(['one']);

        $collection = $augmented->first()->collection();

        if ($parentIsEntry && $handle === 'tags') {
            $this->assertInstanceOf(CollectionContract::class, $collection);
        } else {
            $this->assertNull($collection);
        }
    }

    public function collectionAttachmentProvider()
    {
        return [
            'parent is entry and handle matches taxonomy' => [true, 'tags'],
            'parent is entry and handle does not match taxonomy' => [true, 'related_tags'],
            'parent is not entry and handle matches taxonomy' => [false, 'tags'],
            'parent is not entry and handle does not match taxonomy' => [false, 'related_tags'],
        ];
    }

    /** @test */
    public function using_both_taxonomy_and_taxonomies_throws_an_exception()
    {
        $this->expectExceptionMessage('A terms fieldtype cannot define both `taxonomy` and `taxonomies`. Use `taxonomies`.');

        $this->fieldtype(['taxonomy' => 'categories', 'taxonomies' => 'tags'])->taxonomies();
    }

    /** @test */
    public function having_taxonomy_defined_but_not_taxonomies_throws_an_exception()
    {
        $this->expectExceptionMessage('A terms fieldtype configures its available taxonomies using the `taxonomies` option, but only found `taxonomy`.');

        $this->fieldtype(['taxonomy' => 'categories'])->taxonomies();
    }

    public function fieldtype($config = [], $parent = null, $handle = 'test')
    {
        $field = new Field($handle, array_merge([
            'type' => 'terms',
        ], $config));

        if (! $parent) {
            $parent = EntryFactory::collection('blog')->create(); // necessary?
        }

        $field->setParent($parent);

        return (new Terms)->setField($field);
    }
}
