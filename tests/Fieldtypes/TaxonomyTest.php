<?php

namespace Tests\Fieldtypes;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Collection;
use Statamic\Facades;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Taxonomy;
use Statamic\Statamic;
use Statamic\Taxonomies\LocalizedTerm;
use Statamic\Taxonomies\TermCollection;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TaxonomyTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Facades\Taxonomy::make('tags')->save();
        Facades\Taxonomy::make('categories')->save();
    }

    /** @test */
    function it_augments_slugs_to_a_collection_of_terms_when_using_a_single_taxonomy()
    {
        $augmented = $this->fieldtype(['taxonomy' => 'tags'])->augment(['one', 'two']);

        $this->assertInstanceOf(TermCollection::class, $augmented);
        $this->assertCount(2, $augmented);
        $this->assertEveryItemIsInstanceOf(LocalizedTerm::class, $augmented);
        $this->assertEquals(['tags::one', 'tags::two'], $augmented->map->id()->all());
    }

    /** @test */
    function it_augments_ids_to_a_collection_of_terms_when_using_multiple_taxonomies()
    {
        $augmented = $this->fieldtype(['taxonomy' => ['tags', 'categories']])->augment(['tags::one', 'categories::two']);

        $this->assertInstanceOf(TermCollection::class, $augmented);
        $this->assertCount(2, $augmented);
        $this->assertEveryItemIsInstanceOf(LocalizedTerm::class, $augmented);
        $this->assertEquals(['tags::one', 'categories::two'], $augmented->map->id()->all());
    }

    /** @test */
    function it_throws_an_exception_when_augmenting_a_slug_when_using_multiple_taxonomies()
    {
        $this->expectExceptionMessage("Ambigious taxonomy term value [one]. Field [test] is configured with multiple taxonomies.");

        $this->fieldtype(['taxonomy' => ['tags', 'categories']])->augment(['one', 'two']);
    }

    /** @test */
    function it_augments_to_a_single_term_when_max_items_is_one()
    {
        $augmented = $this->fieldtype(['taxonomy' => 'tags', 'max_items' => 1])->augment(['one']);

        $this->assertInstanceOf(LocalizedTerm::class, $augmented);
        $this->assertEquals('tags::one', $augmented->id());
    }

    /** @test */
    function it_shallow_augments_slugs_to_a_collection_of_terms_when_using_a_single_taxonomy()
    {
        Statamic::enableShallowAugmentation();

        $augmented = $this->fieldtype(['taxonomy' => 'tags'])->augment(['one', 'two']);

        $this->assertInstanceOf(Collection::class, $augmented);
        $this->assertNotInstanceOf(TermCollection::class, $augmented);
        $this->assertCount(2, $augmented);
        $this->assertEquals([
            [
                'id' => 'tags::one',
                'slug' => 'one',
                'url' => '/blog/tags/one',
                'permalink' => 'http://localhost/blog/tags/one',
                'api_url' => 'http://localhost/api/taxonomies/tags/terms/one',
            ],
            [
                'id' => 'tags::two',
                'slug' => 'two',
                'url' => '/blog/tags/two',
                'permalink' => 'http://localhost/blog/tags/two',
                'api_url' => 'http://localhost/api/taxonomies/tags/terms/two',
            ],
        ], $augmented->all());
    }

    /** @test */
    function it_shallow_augments_to_a_single_term_when_max_items_is_one()
    {
        Statamic::enableShallowAugmentation();

        $augmented = $this->fieldtype(['taxonomy' => 'tags', 'max_items' => 1])->augment(['one']);

        $this->assertEquals([
            'id' => 'tags::one',
            'slug' => 'one',
            'url' => '/blog/tags/one',
            'permalink' => 'http://localhost/blog/tags/one',
            'api_url' => 'http://localhost/api/taxonomies/tags/terms/one',
        ], $augmented);
    }

    function fieldtype($config = [])
    {
        $field = new Field('test', array_merge([
            'type' => 'taxonomy',
        ], $config));

        $field->setParent(EntryFactory::collection('blog')->create());

        return (new Taxonomy)->setField($field);
    }
}
