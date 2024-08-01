<?php

namespace Tests\Feature\Entries;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Taxonomy;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class GetByTaxonomyTermsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_gets_entries_by_a_single_taxonomy_term()
    {
        Taxonomy::make('tags')->save();
        Collection::make('blog')->taxonomies(['tags'])->save();
        EntryFactory::collection('blog')->id('1')->data(['tags' => ['rad']])->create();
        EntryFactory::collection('blog')->id('2')->data(['tags' => ['rad']])->create();
        EntryFactory::collection('blog')->id('3')->data(['tags' => ['meh']])->create();

        $this->assertEquals(3, Entry::query()->count());
        $this->assertEquals([1, 2], Entry::query()->whereTaxonomy('tags::rad')->get()->map->id()->all());
    }

    #[Test]
    public function it_gets_entries_in_multiple_taxonomy_terms()
    {
        Taxonomy::make('tags')->save();
        Taxonomy::make('categories')->save();
        Collection::make('blog')->taxonomies(['tags', 'categories'])->save();
        EntryFactory::collection('blog')->id('1')->data(['tags' => ['rad'], 'categories' => ['news']])->create();
        EntryFactory::collection('blog')->id('2')->data(['tags' => ['awesome'], 'categories' => ['events']])->create();
        EntryFactory::collection('blog')->id('3')->data(['tags' => ['rad', 'awesome']])->create();
        EntryFactory::collection('blog')->id('4')->data(['tags' => ['meh']])->create();

        $this->assertEquals(4, Entry::query()->count());
        $this->assertEquals([3], Entry::query()->whereTaxonomy('tags::rad')->whereTaxonomy('tags::awesome')->get()->map->id()->all());
        $this->assertEquals([1], Entry::query()->whereTaxonomy('tags::rad')->whereTaxonomy('categories::news')->get()->map->id()->all());
        $this->assertEquals(0, Entry::query()->whereTaxonomy('tags::rad')->whereTaxonomy('categories::events')->count());
        $this->assertEquals([1, 3, 4], Entry::query()->whereTaxonomyIn(['tags::rad', 'tags::meh'])->get()->map->id()->all());
        $this->assertEquals([1, 2, 3], Entry::query()->whereTaxonomyIn(['tags::rad', 'categories::events'])->get()->map->id()->all());
        $this->assertEquals([3], Entry::query()->whereTaxonomyIn(['tags::rad', 'tags::meh'])->whereTaxonomy('tags::awesome')->get()->map->id()->all());
        $this->assertEquals([2], Entry::query()->whereTaxonomyIn(['tags::meh', 'categories::events'])->whereTaxonomy('tags::awesome')->get()->map->id()->all());
        $this->assertEquals([2], Entry::query()->whereTaxonomyIn(['tags::meh', 'categories::events'])->whereTaxonomyIn(['tags::awesome', 'tags::rad'])->get()->map->id()->all());
    }
}
